<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use App\Models\User;
use App\Services\MidtransClient;
use App\Services\QrisPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class QrisPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['midtrans.server_key' => 'SB-Mid-server-test-key']);
    }

    public function test_start_creates_qris_and_stores_qr_string_and_url(): void
    {
        [$participant, $order] = $this->fixture();
        Http::fake([
            'api.sandbox.midtrans.com/v2/charge' => Http::response([
                'status_code' => '201',
                'transaction_status' => 'pending',
                'qr_string' => 'QRIS-TEST-PAYLOAD',
                'transaction_id' => 'txn-1',
                'actions' => [
                    ['name' => 'generate-qr-code-v2', 'url' => 'https://api.sandbox.midtrans.com/v2/qris/test/qr-code'],
                    ['name' => 'generate-qr-code', 'url' => 'https://evil.example.com/qr'],
                ],
            ], 201),
        ]);

        $this->actingAs($participant)
            ->post(route('payments.start', $order))
            ->assertRedirect(route('orders.show', $order));

        $order->refresh();
        $this->assertSame('QRIS-TEST-PAYLOAD', $order->qr_string);
        $this->assertSame('https://api.sandbox.midtrans.com/v2/qris/test/qr-code', $order->payment_qr_url);
        $this->assertSame('pending', $order->payment_status);
        $this->assertNotNull($order->gateway_order_id);
        Http::assertSent(fn ($r) => $r->url() === 'https://api.sandbox.midtrans.com/v2/charge'
            && $r['payment_type'] === 'qris'
            && $r['transaction_details']['gross_amount'] === 50000
            && ($r['custom_expiry']['order_time'] ?? null) === $order->created_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s O'));
    }

    public function test_start_replay_does_not_recharge_when_qr_active(): void
    {
        [$participant, $order] = $this->fixture(pending: true);
        Http::fake();

        $this->actingAs($participant)->post(route('payments.start', $order))->assertRedirect();

        Http::assertNothingSent();
    }

    public function test_simultaneous_start_second_claim_replays_via_get(): void
    {
        [$participant, $order] = $this->fixture();
        $order->update(['gateway_order_id' => 'EventKu-test-1', 'payment_status' => 'creating']);
        Http::fake([
            'api.sandbox.midtrans.com/v2/charge' => Http::response(['status_code' => '201'], 201),
            'api.sandbox.midtrans.com/v2/*' => Http::response($this->remote('pending', $order), 200),
        ]);

        $this->actingAs($participant)->post(route('payments.start', $order))->assertRedirect();

        Http::assertSentCount(1);
        Http::assertSent(fn ($r) => str_ends_with($r->url(), '/status'));
        Http::assertNotSent(fn ($r) => str_ends_with($r->url(), '/v2/charge'));
        $this->assertSame('pending', $order->refresh()->payment_status);
    }

    public function test_timeout_twice_charges_once_then_get(): void
    {
        [$participant, $order] = $this->fixture();
        $calls = 0;
        Http::fake(function ($request) use (&$calls, $order) {
            if (str_ends_with($request->url(), '/v2/charge')) {
                $calls++;
                if ($calls === 1) {
                    throw new \Illuminate\Http\Client\ConnectionException('timeout');
                }

                return Http::response(['status_code' => '201'], 201);
            }

            return Http::response($this->remote('pending', $order), 200);
        });

        $svc = new QrisPaymentService(new MidtransClient);
        [$ok1] = $svc->start($order->fresh());
        $this->assertFalse($ok1);
        $this->assertSame('error', $order->refresh()->payment_status);
        $gid = $order->refresh()->gateway_order_id;
        $this->assertNotNull($gid);

        [$ok2] = $svc->start($order->fresh());
        $this->assertFalse($ok2); // pending without qr yet: friendly hold
        $this->assertSame($gid, $order->refresh()->gateway_order_id);
        $this->assertSame(1, $calls); // only one POST charge
    }

    public function test_remote_404_after_timeout_holds_without_release(): void
    {
        [$participant, $order] = $this->fixture();
        Http::fake(function ($request) {
            if (str_ends_with($request->url(), '/v2/charge')) {
                throw new \Illuminate\Http\Client\ConnectionException('timeout');
            }

            return Http::response(['status_code' => '404', 'status_message' => 'Not found'], 404);
        });

        $svc = new QrisPaymentService(new MidtransClient);
        [$ok1] = $svc->start($order->fresh());
        $this->assertFalse($ok1);

        $this->actingAs($participant)->post(route('payments.start', $order->fresh()))
            ->assertRedirect();
        $this->assertSame('menunggu', $order->refresh()->status);
        $this->assertNotNull($order->refresh()->gateway_order_id);
    }

    public function test_status_endpoint_applies_settlement_once(): void
    {
        [$participant, $order] = $this->fixture(withGateway: true);
        Http::fake([
            'api.sandbox.midtrans.com/v2/*' => Http::response($this->remote('settlement', $order), 200),
        ]);

        $this->actingAs($participant)->postJson(route('payments.status', $order))
            ->assertOk()
            ->assertJsonPath('status', 'lunas');

        $order->refresh();
        $this->assertSame('lunas', $order->status);
        $this->assertSame(1, $order->tickets()->count());

        $this->actingAs($participant)->postJson(route('payments.status', $order))->assertOk();
        $this->assertSame(1, $order->refresh()->tickets()->count());
    }

    public function test_stale_pending_after_lunas_preserves_settlement(): void
    {
        [$participant, $order] = $this->fixture(withGateway: true);
        $order->update(['status' => 'lunas', 'payment_status' => 'settlement']);
        $order->issueTickets();
        Http::fake([
            'api.sandbox.midtrans.com/v2/*' => Http::response($this->remote('pending', $order), 200),
        ]);

        $this->actingAs($participant)->postJson(route('payments.status', $order))->assertOk();

        $fresh = $order->refresh();
        $this->assertSame('lunas', $fresh->status);
        $this->assertSame('settlement', $fresh->payment_status);
        $this->assertSame(1, $fresh->tickets()->count());
    }

    public function test_late_settlement_at_reserved_last_ticket_fulfills(): void
    {
        [$participant, $order, , $type] = $this->fixture(quota: 1, withGateway: true);
        $order->update(['payment_expires_at' => now()->subMinute()]);
        Http::fake([
            'api.sandbox.midtrans.com/v2/*' => Http::response($this->remote('settlement', $order), 200),
        ]);

        $this->actingAs($participant)->postJson(route('payments.status', $order))->assertOk();

        $this->assertSame('lunas', $order->refresh()->status);
        $this->assertSame(1, $order->refresh()->tickets()->count());
        $this->assertSame(0, $type->refresh()->sisa());
    }

    public function test_late_settlement_after_terminal_cancel_marks_review_no_tickets(): void
    {
        [$participant, $order] = $this->fixture(withGateway: true);
        $order->update(['status' => 'batal', 'payment_status' => 'cancel']);
        Http::fake([
            'api.sandbox.midtrans.com/v2/*' => Http::response($this->remote('settlement', $order), 200),
        ]);

        $this->actingAs($participant)->postJson(route('payments.status', $order))->assertOk();

        $fresh = $order->refresh();
        $this->assertSame('batal', $fresh->status);
        $this->assertSame('settlement_review', $fresh->payment_status);
        $this->assertSame(0, $fresh->tickets()->count());
    }

    public function test_remote_validation_rejects_malformed_identity_and_amount(): void
    {
        [$participant, $order] = $this->fixture(withGateway: true);
        $svc = new QrisPaymentService(new MidtransClient);

        $base = $this->remote('settlement', $order);

        $badOrder = $base;
        $badOrder['order_id'] = 'EventKu-other';
        $this->assertFalse($svc->applyRemoteStatus($order, $badOrder)['ok']);

        $badType = $base;
        $badType['payment_type'] = 'gopay';
        $this->assertFalse($svc->applyRemoteStatus($order, $badType)['ok']);

        $badCurrency = $base;
        $badCurrency['currency'] = 'USD';
        $this->assertFalse($svc->applyRemoteStatus($order, $badCurrency)['ok']);

        $missingCurrency = $base;
        unset($missingCurrency['currency']);
        $this->assertFalse($svc->applyRemoteStatus($order, $missingCurrency)['ok']);

        foreach (['50000.90', 'abc', ''] as $amount) {
            $bad = $base;
            $bad['gross_amount'] = $amount;
            $this->assertFalse($svc->applyRemoteStatus($order, $bad)['ok']);
        }
        $missing = $base;
        unset($missing['gross_amount']);
        $this->assertFalse($svc->applyRemoteStatus($order, $missing)['ok']);

        $badFraud = $base;
        $badFraud['fraud_status'] = 'deny';
        $this->assertFalse($svc->applyRemoteStatus($order, $badFraud)['ok']);

        $capture = $base;
        $capture['transaction_status'] = 'capture';
        $this->assertFalse($svc->applyRemoteStatus($order, $capture)['ok']);

        $this->assertSame('menunggu', $order->refresh()->status);
        $this->assertSame(0, $order->refresh()->tickets()->count());
    }

    public function test_status_endpoint_applies_expire_and_releases_capacity(): void
    {
        [$participant, $order, , $type] = $this->fixture(withGateway: true);
        Http::fake([
            'api.sandbox.midtrans.com/v2/*' => Http::response($this->remote('expire', $order), 200),
        ]);

        $this->actingAs($participant)->postJson(route('payments.status', $order))->assertOk();
        $this->assertSame('batal', $order->refresh()->status);
        $this->assertSame(10, $type->refresh()->sisa());
    }

    public function test_notification_with_valid_signature_settles(): void
    {
        [$participant, $order] = $this->fixture(withGateway: true);
        Http::fake([
            'api.sandbox.midtrans.com/v2/*' => Http::response($this->remote('settlement', $order), 200),
        ]);

        $this->postJson(route('payments.notification'), $this->signed($order, '200', 'settlement'))
            ->assertOk();

        $this->assertSame('lunas', $order->refresh()->status);
        $this->assertSame(1, $order->refresh()->tickets()->count());

        // Duplicate callback issues no extra tickets.
        $this->postJson(route('payments.notification'), $this->signed($order, '200', 'settlement'))->assertOk();
        $this->assertSame(1, $order->refresh()->tickets()->count());
    }

    public function test_notification_rejects_bad_signature_and_mismatched_amount(): void
    {
        [$participant, $order] = $this->fixture(withGateway: true);
        Http::fake();

        $bad = $this->signed($order, '200', 'settlement');
        $bad['signature_key'] = '00';
        $this->postJson(route('payments.notification'), $bad)->assertStatus(400);

        $wrong = $this->signed($order, '200', 'settlement');
        $wrong['gross_amount'] = '1';
        $wrong['signature_key'] = hash('sha512', $order->gateway_order_id.'2001'.config('midtrans.server_key'));
        $this->postJson(route('payments.notification'), $wrong)->assertStatus(422);

        $unknown = $this->signed($order, '200', 'settlement');
        $unknown['order_id'] = 'EventKu-unknown';
        $unknown['signature_key'] = hash('sha512', 'EventKu-unknown20050000'.config('midtrans.server_key'));
        $this->postJson(route('payments.notification'), $unknown)->assertStatus(404);

        $this->assertSame('menunggu', $order->refresh()->status);
        Http::assertNothingSent();
    }

    public function test_checkout_replay_same_key_returns_existing_without_unique_error(): void
    {
        [$participant, $type] = $this->checkoutFixture();
        $key = (string) Str::uuid();
        $payload = ['jumlah' => 1, 'checkout_key' => $key];

        $this->actingAs($participant)->post(route('orders.store', $type), $payload)->assertRedirect();
        $this->actingAs($participant)->post(route('orders.store', $type), $payload)->assertRedirect();

        $this->assertDatabaseCount('orders', 1);
    }

    public function test_gateway_order_blocks_manual_proof_and_organizer_actions(): void
    {
        [$participant, $order, $organizer] = $this->fixture();
        $this->actingAs($participant)->post(route('orders.bukti', $order))->assertForbidden();
        $this->actingAs($organizer)->post(route('organizer.orders.approve', $order))->assertForbidden();
        $this->actingAs($organizer)->post(route('organizer.orders.reject', $order))->assertForbidden();
    }

    private function remote(string $txStatus, Order $order): array
    {
        return [
            'status_code' => '200',
            'transaction_status' => $txStatus,
            'order_id' => $order->gateway_order_id,
            'payment_type' => 'qris',
            'gross_amount' => (string) $order->total,
            'currency' => 'IDR',
            'fraud_status' => 'accept',
        ];
    }

    private function signed(Order $order, string $code, string $txStatus): array
    {
        return [
            'order_id' => $order->gateway_order_id,
            'status_code' => $code,
            'gross_amount' => (string) $order->total,
            'signature_key' => hash('sha512', $order->gateway_order_id.$code.$order->total.config('midtrans.server_key')),
            'transaction_status' => $txStatus,
            'payment_type' => 'qris',
            'currency' => 'IDR',
        ];
    }

    private function baseFixture(int $quota = 10, int $harga = 50000): array
    {
        $participant = User::factory()->create(['role' => 'peserta']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::create([
            'organizer_id' => $organizer->id, 'judul' => 'Tes QRIS terisolasi',
            'deskripsi' => 'Fixture in-memory.', 'kategori' => 'Workshop',
            'tipe' => 'offline', 'lokasi' => 'Ruang tes',
            'tanggal_mulai' => now()->addDay(),
            'tanggal_selesai' => now()->addDay()->addHours(2), 'status' => 'terbit',
        ]);
        $type = TicketType::create(['event_id' => $event->id, 'nama' => 'Reguler', 'harga' => $harga, 'kuota' => $quota]);

        return [$participant, $organizer, $event, $type];
    }

    private function fixture(bool $pending = false, bool $withGateway = false, int $quota = 10): array
    {
        [$participant, $organizer, $event, $type] = $this->baseFixture($quota);
        $order = Order::create([
            'user_id' => $participant->id, 'ticket_type_id' => $type->id,
            'jumlah' => 1, 'total' => 50000, 'status' => 'menunggu',
            'payment_method' => 'midtrans_qris',
            'payment_status' => $pending ? 'pending' : 'created',
            'qr_string' => $pending ? 'QRIS-EXISTING' : null,
            'payment_expires_at' => now()->addMinutes(15),
        ]);
        if ($withGateway || $pending) {
            $order->update(['gateway_order_id' => 'EventKu-test-'.$order->id.'-'.uniqid()]);
            $order->refresh();
        }

        return [$participant, $order, $organizer, $type];
    }

    private function checkoutFixture(): array
    {
        [$participant, $organizer, $event, $type] = $this->baseFixture();

        return [$participant, $type];
    }
}

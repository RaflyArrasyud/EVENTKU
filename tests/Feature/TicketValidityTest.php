<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use App\Models\User;
use App\Services\MidtransClient;
use App\Services\QrisPaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TicketValidityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['midtrans.server_key' => 'SB-Mid-server-test-key']);
    }

    public function test_first_settlement_uses_provider_local_timestamp_and_replay_preserves(): void
    {
        [$participant, $order] = $this->gatewayFixture();
        $remote = $this->remote('settlement', $order);
        $remote['settlement_time'] = '2026-10-11 14:30:00 +0700';
        $remote['transaction_id'] = 'txn-settle-1';
        Http::fake(['api.sandbox.midtrans.com/v2/*' => Http::response($remote, 200)]);

        $this->actingAs($participant)->postJson(route('payments.status', $order))->assertOk();

        $fresh = $order->refresh();
        $this->assertSame('lunas', $fresh->status);
        $this->assertSame('txn-settle-1', $fresh->gateway_transaction_id);
        $this->assertTrue($fresh->paid_at->equalTo(Carbon::parse('2026-10-11 14:30:00 +0700')->timezone(config('app.timezone'))));

        // Replay with different provider time must not overwrite.
        $replay = $remote;
        $replay['settlement_time'] = '2026-10-12 10:00:00 +0700';
        $replay['transaction_id'] = 'txn-changed';
        (new QrisPaymentService(new MidtransClient))->applyRemoteStatus($fresh, $replay);

        $again = $order->refresh();
        $this->assertTrue($again->paid_at->equalTo($fresh->paid_at));
        $this->assertSame('txn-settle-1', $again->gateway_transaction_id);
        $this->assertSame('lunas', $again->status);
    }

    public function test_settlement_without_provider_time_uses_now_and_legacy_stays_unknown(): void
    {
        [$participant, $order] = $this->gatewayFixture();
        Carbon::setTestNow(Carbon::parse('2026-10-11 12:00:00 UTC'));
        Http::fake(['api.sandbox.midtrans.com/v2/*' => Http::response($this->remote('settlement', $order), 200)]);

        $this->actingAs($participant)->postJson(route('payments.status', $order))->assertOk();
        $this->assertTrue($order->refresh()->paid_at->equalTo(now()));
        Carbon::setTestNow();

        // Legacy paid order without paid_at keeps null (unknown, not updated_at).
        [$p2, $legacy] = $this->gatewayFixture();
        $legacy->update(['status' => 'lunas', 'payment_status' => 'settlement', 'paid_at' => null]);
        $svc = new QrisPaymentService(new MidtransClient);
        $svc->applyRemoteStatus($legacy, $this->remote('settlement', $legacy));
        $this->assertNull($legacy->refresh()->paid_at);
    }

    public function test_lunas_with_paid_at_shortcuts_without_remote_call(): void
    {
        [$participant, $order] = $this->gatewayFixture();
        $order->update(['status' => 'lunas', 'payment_status' => 'settlement', 'paid_at' => now()]);
        Http::fake();

        $this->actingAs($participant)->postJson(route('payments.status', $order))
            ->assertOk()->assertJsonPath('status', 'lunas');

        Http::assertNothingSent();
    }

    public function test_lunas_without_paid_at_backfills_verified_time_only(): void
    {
        [$participant, $order] = $this->gatewayFixture();
        $order->update(['status' => 'lunas', 'payment_status' => 'settlement', 'paid_at' => null]);
        $order->issueTickets();
        $remote = $this->remote('settlement', $order);
        $remote['settlement_time'] = '2026-10-11 14:30:00 +0700';
        $remote['transaction_id'] = 'txn-backfill-1';
        Http::fake(['api.sandbox.midtrans.com/v2/*' => Http::response($remote, 200)]);

        $this->actingAs($participant)->postJson(route('payments.status', $order))
            ->assertOk()->assertJsonPath('status', 'lunas');

        $fresh = $order->refresh();
        $this->assertSame('lunas', $fresh->status);
        $this->assertSame('settlement', $fresh->payment_status);
        $this->assertSame('txn-backfill-1', $fresh->gateway_transaction_id);
        $this->assertTrue($fresh->paid_at->equalTo(Carbon::parse('2026-10-11 14:30:00 +0700')->timezone(config('app.timezone'))));
        $this->assertSame(1, $fresh->tickets()->count());
        Http::assertNotSent(fn ($r) => str_ends_with($r->url(), '/v2/charge'));

        // Idempotent: second call keeps verified time, no duplicate tickets.
        $this->actingAs($participant)->postJson(route('payments.status', $order))->assertOk();
        $this->assertTrue($order->refresh()->paid_at->equalTo($fresh->paid_at));
        $this->assertSame(1, $order->refresh()->tickets()->count());
    }

    public function test_lunas_without_paid_at_stays_unknown_without_verified_time_or_on_network_failure(): void
    {
        // Remote settlement without settlement_time: no fabricated now().
        [$participant, $order] = $this->gatewayFixture();
        $order->update(['status' => 'lunas', 'payment_status' => 'settlement', 'paid_at' => null]);
        Http::fake(['api.sandbox.midtrans.com/v2/*' => Http::response($this->remote('settlement', $order), 200)]);

        $this->actingAs($participant)->postJson(route('payments.status', $order))
            ->assertOk()->assertJsonPath('status', 'lunas');
        $this->assertNull($order->refresh()->paid_at);
        $this->assertSame('lunas', $order->refresh()->status);

        // Network failure: paid stays stable, still lunas.
        [$participant2, $order2] = $this->gatewayFixture();
        $order2->update(['status' => 'lunas', 'payment_status' => 'settlement', 'paid_at' => null]);
        Http::fake(function () {
            throw new \Illuminate\Http\Client\ConnectionException('timeout');
        });

        $this->actingAs($participant2)->postJson(route('payments.status', $order2))
            ->assertOk()->assertJsonPath('status', 'lunas');
        $this->assertNull($order2->refresh()->paid_at);
        $this->assertSame('lunas', $order2->refresh()->status);
        $this->assertSame('settlement', $order2->refresh()->payment_status);
    }

    public function test_wrong_identity_changes_nothing(): void
    {
        [, $order] = $this->gatewayFixture();
        $svc = new QrisPaymentService(new MidtransClient);
        $bad = $this->remote('settlement', $order);
        $bad['order_id'] = 'EventKu-other';

        $this->assertFalse($svc->applyRemoteStatus($order, $bad)['ok']);
        $fresh = $order->refresh();
        $this->assertSame('menunggu', $fresh->status);
        $this->assertNull($fresh->paid_at);
        $this->assertNull($fresh->gateway_transaction_id);
    }

    public function test_free_checkout_and_manual_approval_set_paid_at(): void
    {
        [$participant, $freeType] = $this->freeFixture();
        $this->actingAs($participant)->post(route('orders.store', $freeType), ['jumlah' => 1])->assertRedirect();
        $free = Order::sole();
        $this->assertSame('lunas', $free->status);
        $this->assertNotNull($free->paid_at);

        [$participant2, $order, $organizer] = $this->manualFixture();
        $this->actingAs($organizer)->post(route('organizer.orders.approve', $order))->assertRedirect();
        $this->assertSame('lunas', $order->refresh()->status);
        $this->assertNotNull($order->refresh()->paid_at);
    }

    public function test_checkin_rejects_after_end_and_at_exact_end_without_mutation(): void
    {
        foreach (['+1 hour', 'exact'] as $case) {
            [$participant, $ticket, $organizer] = $this->ticketFixture();
            $end = $ticket->order->ticketType->event->tanggal_selesai;
            Carbon::setTestNow($case === 'exact' ? $end->copy() : $end->copy()->addHour());

            $this->actingAs($organizer)->post(route('organizer.checkin.store'), ['kode' => $ticket->kode])
                ->assertRedirect()->assertSessionHas('error', 'Masa berlaku tiket sudah berakhir.');

            $fresh = $ticket->refresh();
            $this->assertSame('aktif', $fresh->status);
            $this->assertNull($fresh->waktu_checkin);
            $this->assertTrue($fresh->isExpired());
            Carbon::setTestNow();
        }
    }

    public function test_checkin_before_end_succeeds_and_duplicate_keeps_first_time(): void
    {
        [$participant, $ticket, $organizer] = $this->ticketFixture();
        $event = $ticket->order->ticketType->event;
        Carbon::setTestNow($event->tanggal_selesai->copy()->subHour());

        $this->actingAs($organizer)->post(route('organizer.checkin.store'), ['kode' => $ticket->kode])
            ->assertRedirect()->assertSessionHas('success');
        $first = $ticket->refresh()->waktu_checkin;
        $this->assertSame('terpakai', $ticket->refresh()->status);

        $this->actingAs($organizer)->post(route('organizer.checkin.store'), ['kode' => $ticket->kode])
            ->assertRedirect()->assertSessionHas('error', 'Tiket ini sudah dipakai.');
        $this->assertTrue($ticket->refresh()->waktu_checkin->equalTo($first));
        Carbon::setTestNow();
    }

    public function test_checkin_before_start_allowed_and_owner_unpaid_guarded(): void
    {
        [$participant, $ticket, $organizer] = $this->ticketFixture();
        Carbon::setTestNow($ticket->order->ticketType->event->tanggal_mulai->copy()->subHour());
        $this->actingAs($organizer)->post(route('organizer.checkin.store'), ['kode' => $ticket->kode])
            ->assertRedirect()->assertSessionHas('success');
        Carbon::setTestNow();

        // Unpaid order never reaches scanner success.
        [$p2, $ticket2, $organizer2] = $this->ticketFixture(paid: false);
        $this->actingAs($organizer2)->post(route('organizer.checkin.store'), ['kode' => $ticket2->kode])
            ->assertRedirect()->assertSessionHas('error', 'Pesanan tiket ini belum lunas.');
        $this->assertSame('aktif', $ticket2->refresh()->status);

        // Other organizer sees no leakage.
        $stranger = User::factory()->create(['role' => 'organizer']);
        [$p3, $ticket3] = $this->ticketFixture();
        $this->actingAs($stranger)->post(route('organizer.checkin.store'), ['kode' => $ticket3->kode])
            ->assertRedirect()->assertSessionHas('error', 'Kode tiket tidak ditemukan.');
        $this->assertSame('aktif', $ticket3->refresh()->status);
    }

    private function baseEvent(User $organizer, $start, $end): array
    {
        $event = Event::create([
            'organizer_id' => $organizer->id, 'judul' => 'Tes validitas',
            'deskripsi' => 'Fixture in-memory.', 'kategori' => 'Workshop',
            'tipe' => 'offline', 'lokasi' => 'Ruang tes',
            'tanggal_mulai' => $start, 'tanggal_selesai' => $end, 'status' => 'terbit',
        ]);
        $type = TicketType::create(['event_id' => $event->id, 'nama' => 'Reguler', 'harga' => 50000, 'kuota' => 10]);

        return [$event, $type];
    }

    private function gatewayFixture(): array
    {
        $participant = User::factory()->create(['role' => 'peserta']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        [, $type] = $this->baseEvent($organizer, now()->addDay(), now()->addDay()->addHours(2));
        $order = Order::create([
            'user_id' => $participant->id, 'ticket_type_id' => $type->id,
            'jumlah' => 1, 'total' => 50000, 'status' => 'menunggu',
            'payment_method' => 'midtrans_qris', 'payment_status' => 'created',
            'payment_expires_at' => now()->addMinutes(15),
            'gateway_order_id' => 'EventKu-test-'.uniqid(),
        ]);

        return [$participant, $order->refresh(), $organizer, $type];
    }

    private function freeFixture(): array
    {
        $participant = User::factory()->create(['role' => 'peserta']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        [, $type] = $this->baseEvent($organizer, now()->addDay(), now()->addDay()->addHours(2));
        $type->update(['harga' => 0]);

        return [$participant, $type];
    }

    private function manualFixture(): array
    {
        $participant = User::factory()->create(['role' => 'peserta']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        [, $type] = $this->baseEvent($organizer, now()->addDay(), now()->addDay()->addHours(2));
        $type->update(['harga' => 0, 'kuota' => 10]);
        $order = Order::create([
            'user_id' => $participant->id, 'ticket_type_id' => $type->id,
            'jumlah' => 1, 'total' => 10000, 'status' => 'menunggu', 'payment_method' => 'manual',
        ]);

        return [$participant, $order, $organizer];
    }

    private function ticketFixture(bool $paid = true): array
    {
        $participant = User::factory()->create(['role' => 'peserta']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        [, $type] = $this->baseEvent($organizer, now()->addDay(), now()->addDay()->addHours(2));
        $order = Order::create([
            'user_id' => $participant->id, 'ticket_type_id' => $type->id,
            'jumlah' => 1, 'total' => 0, 'status' => $paid ? 'lunas' : 'menunggu',
            'paid_at' => $paid ? now() : null, 'payment_method' => 'manual',
        ]);
        $ticket = $paid ? tap($order)->issueTickets()->tickets()->first() : $order->tickets()->create(['kode' => 'UNPAIDTEST123']);

        return [$participant, $ticket->refresh(), $organizer];
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
}

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
use Tests\TestCase;

class PaymentReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_scheduled_reconciliation_checks_payment_before_expiry(): void
    {
        $order = $this->fixture();
        config(['midtrans.server_key' => 'sandbox-test-key']);
        Http::fake(['api.sandbox.midtrans.com/v2/*' => Http::response($this->remote($order), 200)]);

        $result = $this->service()->reconcileDue();

        $this->assertSame(1, $result['settled']);
        $this->assertSame('lunas', $order->fresh()->status);
        $this->assertSame(1, $order->tickets()->count());
    }

    public function test_local_expiry_releases_only_unattempted_created_order(): void
    {
        $order = $this->fixture();
        $order->update(['gateway_order_id' => null, 'payment_status' => 'created', 'payment_expires_at' => now()->subMinute()]);
        Http::fake();

        $result = $this->service()->reconcileDue();

        $this->assertSame(1, $result['released']);
        $this->assertSame('batal', $order->fresh()->status);
        Http::assertNothingSent();
    }

    public function test_network_failure_holds_pending_quota(): void
    {
        $order = $this->fixture();
        $order->update(['payment_expires_at' => now()->subMinute()]);
        config(['midtrans.server_key' => 'sandbox-test-key']);
        Http::fake(['*' => Http::response(['status_code' => '500'], 500)]);

        $result = $this->service()->reconcileDue();

        $this->assertSame(1, $result['held']);
        $this->assertSame('menunggu', $order->fresh()->status);
        $this->assertSame(9, $order->ticketType->sisa());
    }

    public function test_midtrans_client_does_not_send_empty_key_or_use_production_url(): void
    {
        Http::fake();
        config(['midtrans.server_key' => '']);
        try {
            app(MidtransClient::class)->transactionStatus('test-id');
            $this->fail('An empty Server Key must not be sent.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('not configured', $e->getMessage());
        }
        Http::assertNothingSent();

        config(['midtrans.server_key' => 'sandbox-test-key', 'midtrans.base_url' => 'https://api.midtrans.com']);
        app(MidtransClient::class)->transactionStatus('test-id');
        Http::assertSent(fn ($request) => $request->url() === 'https://api.sandbox.midtrans.com/v2/test-id/status');
    }

    public function test_unknown_remote_status_is_not_saved(): void
    {
        $order = $this->fixture();
        $remote = $this->remote($order);
        $remote['transaction_status'] = 'unknown-status';

        $result = $this->service()->applyRemoteStatus($order, $remote);

        $this->assertFalse($result['ok']);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    private function service(): QrisPaymentService
    {
        return new QrisPaymentService(app(MidtransClient::class));
    }

    private function remote(Order $order): array
    {
        return ['status_code' => '200', 'order_id' => $order->gateway_order_id,
            'payment_type' => 'qris', 'currency' => 'IDR', 'gross_amount' => '50000.00',
            'transaction_status' => 'settlement'];
    }

    private function fixture(): Order
    {
        $participant = User::factory()->create(['role' => 'peserta']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::create([
            'organizer_id' => $organizer->id, 'judul' => 'Tes rekonsiliasi',
            'deskripsi' => 'Fixture in-memory.', 'kategori' => 'Workshop',
            'tipe' => 'offline', 'lokasi' => 'Ruang tes',
            'tanggal_mulai' => now()->addDay(), 'tanggal_selesai' => now()->addDay()->addHours(2),
            'status' => 'terbit',
        ]);
        $type = TicketType::create(['event_id' => $event->id, 'nama' => 'Reguler', 'harga' => 50000, 'kuota' => 10]);

        return Order::create([
            'user_id' => $participant->id, 'ticket_type_id' => $type->id,
            'jumlah' => 1, 'total' => 50000, 'status' => 'menunggu',
            'payment_method' => 'midtrans_qris', 'payment_status' => 'pending',
            'gateway_order_id' => 'EventKu-reconcile-test', 'payment_expires_at' => now()->addMinutes(15),
        ]);
    }
}

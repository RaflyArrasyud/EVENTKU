<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateway_order_does_not_show_manual_bank_or_upload_form(): void
    {
        [$participant, $order] = $this->fixture();
        config(['services.midtrans.server_key' => '', 'midtrans.server_key' => '']);

        $this->actingAs($participant)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('QRIS')
            ->assertSee('Pembayaran demo belum diaktifkan')
            ->assertDontSee('BCA 1234567890')
            ->assertDontSee('name="bukti"', false);
    }

    public function test_legacy_manual_order_keeps_csrf_protected_upload(): void
    {
        [$participant, $order] = $this->fixture('manual');

        $this->actingAs($participant)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('name="bukti"', false)
            ->assertSee('name="_token"', false);
    }

    public function test_gateway_order_has_no_manual_approval_actions_in_organizer_list(): void
    {
        [, $order, $organizer] = $this->fixture();

        $this->actingAs($organizer)->get(route('organizer.orders.index'))
            ->assertOk()
            ->assertSee('QRIS')
            ->assertDontSee('action="'.route('organizer.orders.approve', $order).'"', false)
            ->assertDontSee('action="'.route('organizer.orders.reject', $order).'"', false);
    }

    public function test_paid_order_still_displays_checkin_ticket_qr(): void
    {
        [$participant, $order] = $this->fixture();
        $order->update(['status' => 'lunas', 'payment_status' => 'settlement']);
        $order->issueTickets();
        $code = $order->tickets()->firstOrFail()->kode;

        $this->actingAs($participant)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee($code)
            ->assertSee('<svg', false);
    }

    public function test_detail_separates_payment_deadline_from_ticket_validity_and_uses_wib(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-11 09:00:00', 'UTC'));
        [$participant, $order] = $this->fixture();
        $order->update(['status' => 'lunas', 'payment_status' => 'settlement', 'paid_at' => now()->addMinutes(2)]);
        $order->issueTickets();

        $this->actingAs($participant)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Waktu transaksi')
            ->assertSee('11 Okt 2026, 16:00 WIB')
            ->assertSee('11 Okt 2026, 16:02 WIB')
            ->assertSee('Batas pembayaran QRIS')
            ->assertSee('11 Okt 2026, 16:15 WIB')
            ->assertSee('Batas ini bukan masa berlaku tiket')
            ->assertSee('12 Okt 2026, 18:00 WIB')
            ->assertSee('data-checkin-qr', false);
    }

    public function test_legacy_paid_order_does_not_invent_confirmation_timestamp(): void
    {
        [$participant, $order] = $this->fixture('manual');
        $order->update(['status' => 'lunas']);

        $this->actingAs($participant)->get(route('orders.show', $order))
            ->assertOk()->assertSee('Waktu konfirmasi belum tercatat');
    }

    public function test_expired_ticket_hides_checkin_qr_but_order_remains_paid(): void
    {
        [$participant, $order] = $this->fixture();
        $order->update(['status' => 'lunas', 'payment_status' => 'settlement']);
        $order->issueTickets();
        $order->ticketType->event->update(['tanggal_selesai' => now()]);

        $this->actingAs($participant)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Status: Kedaluwarsa')
            ->assertSee('QR check-in sudah tidak berlaku')
            ->assertDontSee('data-checkin-qr', false)
            ->assertSee('Lunas');
        $this->assertSame('lunas', $order->fresh()->status);
        $this->assertSame('aktif', $order->tickets()->first()->status);
    }

    public function test_used_ticket_keeps_certificate_link_but_cannot_scan_again(): void
    {
        [$participant, $order] = $this->fixture();
        $order->update(['status' => 'lunas']);
        $order->issueTickets();
        $ticket = $order->tickets()->first();
        $ticket->update(['status' => 'terpakai', 'waktu_checkin' => now()]);

        $this->actingAs($participant)->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Status: Terpakai')
            ->assertSee('Lihat E-Sertifikat')
            ->assertSee('Check-in:')
            ->assertSee('QR sudah digunakan')
            ->assertDontSee('data-checkin-qr', false);
    }

    public function test_list_shows_order_number_and_transaction_date_without_changing_payment_badge(): void
    {
        [$participant, $order] = $this->fixture();
        $order->update(['status' => 'lunas']);
        $order->ticketType->event->update(['tanggal_selesai' => now()->subMinute()]);

        $this->actingAs($participant)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Pesanan #'.$order->id)
            ->assertSee('WIB')
            ->assertSee('Event selesai')
            ->assertSee('Lunas')
            ->assertSee('Detail Tiket');
    }

    private function fixture(string $method = 'midtrans_qris'): array
    {
        $participant = User::factory()->create(['role' => 'peserta']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::create([
            'organizer_id' => $organizer->id,
            'judul' => 'Workshop pembayaran terisolasi',
            'deskripsi' => 'Fixture database in-memory.',
            'kategori' => 'Workshop', 'tipe' => 'offline', 'lokasi' => 'Ruang tes',
            'tanggal_mulai' => now()->addDay(),
            'tanggal_selesai' => now()->addDay()->addHours(2), 'status' => 'terbit',
        ]);
        $type = TicketType::create(['event_id' => $event->id, 'nama' => 'Reguler', 'harga' => 50000, 'kuota' => 100]);
        $order = Order::create([
            'user_id' => $participant->id, 'ticket_type_id' => $type->id,
            'jumlah' => 1, 'total' => 50000, 'status' => 'menunggu',
            'payment_method' => $method, 'payment_status' => 'created',
            'payment_expires_at' => now()->addMinutes(15),
        ]);

        return [$participant, $order, $organizer];
    }
}

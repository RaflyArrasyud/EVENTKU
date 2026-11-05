<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutReservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_replaying_checkout_key_does_not_create_duplicate_tickets(): void
    {
        [$participant, $type] = $this->fixture();
        $payload = ['jumlah' => 1, 'checkout_key' => (string) Str::uuid()];

        $this->actingAs($participant)->post(route('orders.store', $type), $payload)->assertRedirect();
        $this->post(route('orders.store', $type), $payload)->assertRedirect();

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('tickets', 1);
    }

    public function test_same_checkout_key_cannot_be_reused_with_a_different_quantity(): void
    {
        [$participant, $type] = $this->fixture();
        $key = (string) Str::uuid();
        $this->actingAs($participant)->post(route('orders.store', $type), ['jumlah' => 1, 'checkout_key' => $key]);
        $this->post(route('orders.store', $type), ['jumlah' => 2, 'checkout_key' => $key]);

        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(1, (int) Order::sole()->jumlah);
    }

    public function test_second_buyer_cannot_take_a_paid_last_ticket(): void
    {
        [$participant, $type] = $this->fixture(1);
        $this->actingAs($participant)->post(route('orders.store', $type), [
            'jumlah' => 1, 'checkout_key' => (string) Str::uuid(),
        ])->assertRedirect();
        $other = User::factory()->create(['role' => 'peserta']);

        $this->actingAs($other)->post(route('orders.store', $type), [
            'jumlah' => 1, 'checkout_key' => (string) Str::uuid(),
        ])->assertSessionHasErrors('jumlah');

        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(0, $type->sisa());
    }

    public function test_pending_order_reserves_capacity_and_cancel_releases_it(): void
    {
        [$participant, $type] = $this->fixture(1);
        $order = Order::create([
            'user_id' => $participant->id, 'ticket_type_id' => $type->id,
            'jumlah' => 1, 'total' => 50000, 'status' => 'menunggu',
        ]);

        $this->assertSame(0, $type->sisa());
        $order->update(['status' => 'batal']);
        $this->assertSame(1, $type->sisa());
    }

    public function test_client_cannot_override_ticket_price_or_paid_status(): void
    {
        [$participant, $type] = $this->fixture();
        $this->actingAs($participant)->post(route('orders.store', $type), [
            'jumlah' => 2, 'checkout_key' => (string) Str::uuid(),
            'total' => 999999, 'status' => 'batal', 'user_id' => 9999,
        ])->assertRedirect();

        $order = Order::sole();
        $this->assertSame(0, (int) $order->total);
        $this->assertSame('lunas', $order->status);
        $this->assertSame($participant->id, $order->user_id);
    }

    private function fixture(int $quota = 10): array
    {
        $participant = User::factory()->create(['role' => 'peserta']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::create([
            'organizer_id' => $organizer->id, 'judul' => 'Tes kuota checkout',
            'deskripsi' => 'Fixture database in-memory.', 'kategori' => 'Workshop',
            'tipe' => 'offline', 'lokasi' => 'Ruang tes',
            'tanggal_mulai' => now()->addDay(),
            'tanggal_selesai' => now()->addDay()->addHours(2), 'status' => 'terbit',
        ]);
        $type = TicketType::create(['event_id' => $event->id, 'nama' => 'Gratis', 'harga' => 0, 'kuota' => $quota]);

        return [$participant, $type];
    }
}

<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderProofTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_view_proof_inline_with_private_headers(): void
    {
        [$participant, $order] = $this->fixture();
        Storage::fake('public');
        $file = UploadedFile::fake()->image('bukti.png');
        $order->update(['bukti_bayar' => $file->store('bukti', 'public')]);

        $response = $this->actingAs($participant)->get(route('orders.proof', $order));
        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Type', 'image/png');
        $cache = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('private', $cache);
        $this->assertStringContainsString('no-store', $cache);
        $this->assertStringStartsWith('inline', $response->headers->get('Content-Disposition'));
    }

    public function test_owning_organizer_can_view_proof(): void
    {
        [$participant, $order, $organizer] = $this->fixture();
        Storage::fake('public');
        $file = UploadedFile::fake()->image('bukti.png');
        $order->update(['bukti_bayar' => $file->store('bukti', 'public')]);

        $this->actingAs($organizer)->get(route('orders.proof', $order))->assertOk();
    }

    public function test_unrelated_user_and_admin_denied(): void
    {
        [$participant, $order] = $this->fixture();
        Storage::fake('public');
        $file = UploadedFile::fake()->image('bukti.png');
        $order->update(['bukti_bayar' => $file->store('bukti', 'public')]);
        $other = User::factory()->create(['role' => 'peserta']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($other)->get(route('orders.proof', $order))->assertForbidden();
        $this->actingAs($admin)->get(route('orders.proof', $order))->assertForbidden();
    }

    public function test_missing_proof_traversal_and_other_folder_return_404(): void
    {
        [$participant, $order] = $this->fixture();

        $this->actingAs($participant)->get(route('orders.proof', $order))->assertNotFound();

        $order->update(['bukti_bayar' => 'bukti/../.env']);
        $this->actingAs($participant)->get(route('orders.proof', $order))->assertNotFound();

        $order->update(['bukti_bayar' => 'avatars/x.png']);
        $this->actingAs($participant)->get(route('orders.proof', $order))->assertNotFound();

        $order->update(['bukti_bayar' => 'bukti/gone.png']);
        $this->actingAs($participant)->get(route('orders.proof', $order))->assertNotFound();
    }

    public function test_guest_redirected_and_gateway_without_proof_404(): void
    {
        [$participant, $order] = $this->fixture();

        $this->get(route('orders.proof', $order))->assertRedirect(route('login'));

        $order->update(['payment_method' => 'midtrans_qris', 'bukti_bayar' => null]);
        $this->actingAs($participant)->get(route('orders.proof', $order))->assertNotFound();
    }

    private function fixture(): array
    {
        $participant = User::factory()->create(['role' => 'peserta']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::create([
            'organizer_id' => $organizer->id,
            'judul' => 'Workshop bukti',
            'deskripsi' => 'Fixture.',
            'kategori' => 'Workshop', 'tipe' => 'offline', 'lokasi' => 'Ruang tes',
            'tanggal_mulai' => now()->addDay(),
            'tanggal_selesai' => now()->addDay()->addHours(2), 'status' => 'terbit',
        ]);
        $type = TicketType::create(['event_id' => $event->id, 'nama' => 'Reguler', 'harga' => 50000, 'kuota' => 100]);
        $order = Order::create([
            'user_id' => $participant->id, 'ticket_type_id' => $type->id,
            'jumlah' => 1, 'total' => 50000, 'status' => 'menunggu',
            'payment_method' => 'manual',
        ]);

        return [$participant, $order, $organizer];
    }
}

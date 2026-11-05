<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CheckoutCsrfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Laravel normally skips CSRF in tests. Exercise the real protection here.
        $this->app->bind(VerifyCsrfToken::class, function ($app) {
            return new class($app, $app['encrypter']) extends VerifyCsrfToken
            {
                protected function runningUnitTests()
                {
                    return false;
                }
            };
        });
    }

    public function test_purchase_form_includes_csrf_token(): void
    {
        [$participant, $type] = $this->fixture();

        $this->actingAs($participant)
            ->get(route('events.show', $type->event))
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_purchase_without_csrf_is_still_rejected(): void
    {
        [$participant, $type] = $this->fixture();

        $this->actingAs($participant)
            ->withSession(['_token' => 'checkout-test-token'])
            ->post(route('orders.store', $type), [
                'jumlah' => 1,
                'checkout_key' => (string) Str::uuid(),
            ])
            ->assertStatus(419);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_purchase_with_valid_csrf_creates_order_and_tickets(): void
    {
        [$participant, $type] = $this->fixture();

        $response = $this->actingAs($participant)
            ->withSession(['_token' => 'checkout-test-token'])
            ->post(route('orders.store', $type), [
                '_token' => 'checkout-test-token',
                'jumlah' => 2,
                'checkout_key' => (string) Str::uuid(),
            ]);

        $order = Order::sole();
        $response->assertRedirect(route('orders.show', $order));
        $this->assertSame('lunas', $order->status);
        $this->assertSame($participant->id, $order->user_id);
        $this->assertSame(2, $order->tickets()->count());
    }

    public function test_order_view_cannot_be_opened_by_another_participant(): void
    {
        [$participant, $type] = $this->fixture();
        $order = Order::create([
            'user_id' => $participant->id,
            'ticket_type_id' => $type->id,
            'jumlah' => 1, 'total' => 0, 'status' => 'lunas',
        ]);
        $other = User::factory()->create(['role' => 'peserta']);

        $this->actingAs($other)->get(route('orders.show', $order))->assertForbidden();
    }

    public function test_browser_payment_routes_require_csrf_too(): void
    {
        [$participant, $type] = $this->fixture();
        $order = Order::create([
            'user_id' => $participant->id, 'ticket_type_id' => $type->id,
            'jumlah' => 1, 'total' => 50000, 'status' => 'menunggu',
            'payment_method' => 'midtrans_qris', 'payment_status' => 'created',
        ]);

        foreach (['payments.start', 'payments.status'] as $route) {
            $this->actingAs($participant)->withSession(['_token' => 'actual-token'])
                ->post(route($route, $order))->assertStatus(419);
        }

        $this->assertSame('menunggu', $order->fresh()->status);
        $this->assertDatabaseCount('tickets', 0);
    }

    private function fixture(): array
    {
        $participant = User::factory()->create(['role' => 'peserta']);
        $organizer = User::factory()->create(['role' => 'organizer']);
        $event = Event::create([
            'organizer_id' => $organizer->id,
            'judul' => 'Event pengujian CSRF',
            'deskripsi' => 'Data hanya berada dalam database tes terisolasi.',
            'kategori' => 'Workshop',
            'tipe' => 'offline',
            'lokasi' => 'Ruang pengujian',
            'tanggal_mulai' => now()->addDay(),
            'tanggal_selesai' => now()->addDay()->addHours(2),
            'status' => 'terbit',
        ]);
        $type = TicketType::create([
            'event_id' => $event->id,
            'nama' => 'Gratis',
            'harga' => 0,
            'kuota' => 10,
        ]);

        return [$participant, $type];
    }
}

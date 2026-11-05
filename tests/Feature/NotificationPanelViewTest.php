<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPanelViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_notif_panel_stays_in_mobile_viewport(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')
            ->assertOk()
            // Sheet full-width di HP (jangkar tombol meluber di 320-414px), dropdown kanan di sm+.
            ->assertSee('inset-x-4 top-full', false)
            ->assertSee('sm:right-0 sm:mt-3 sm:w-72', false)
            // Tinggi nyaman + teks panjang wrap.
            ->assertSee('max-h-[60vh]', false)
            ->assertSee('break-words', false);
    }

    public function test_notif_empty_state_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSee('Belum ada notifikasi.');
    }
}

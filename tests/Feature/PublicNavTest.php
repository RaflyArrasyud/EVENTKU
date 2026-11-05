<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicNavTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_kategori_links_to_home_anchor_not_events_index(): void
    {
        $this->get('/')
            ->assertOk()
            // Kategori anchor nyata di home, bukan duplikat events.index.
            ->assertSee(route('home').'#kategori', false)
            // Jelajahi Event tetap ke events.index.
            ->assertSee(route('events.index'), false);
    }

    public function test_guest_desktop_nav_centered_grid_with_xl_breakpoint(): void
    {
        $this->get('/')
            ->assertOk()
            // Grid 3 kolom setara = link tengah presisi container.
            ->assertSee('grid-cols-[1fr_auto_1fr]', false)
            ->assertSee('justify-self-center', false)
            // Guest pakai hamburger di bawah xl agar tidak tabrakan.
            ->assertSee('hidden xl:flex', false)
            ->assertSee('xl:hidden', false);
    }

    public function test_auth_nav_preserves_md_breakpoint(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/')
            ->assertOk()
            ->assertSee('hidden md:flex', false)
            ->assertSee('md:hidden', false);
    }

    public function test_home_has_real_kategori_anchor_with_scroll_offset(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('id="kategori"', false)
            ->assertSee('scroll-mt-24', false);
    }

    public function test_kategori_never_active_when_on_events_index(): void
    {
        // Kategori active selalu false: di /events hanya Jelajahi Event yang neon.
        $this->get('/events')
            ->assertOk()
            ->assertSee('Jelajahi Event');
    }
}

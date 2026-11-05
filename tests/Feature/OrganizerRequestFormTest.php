<?php

namespace Tests\Feature;

use App\Models\OrganizerRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizerRequestFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_request_form_has_labels_and_explicit_theme_colors(): void
    {
        $user = User::factory()->create(['role' => 'peserta']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-organizer-request-form', false)
            ->assertSee('for="organizer-alasan"', false)
            ->assertSee('for="organizer-kontak"', false)
            ->assertSee('bg-white dark:bg-deep text-slate-900 dark:text-paper', false)
            ->assertSee('placeholder:text-slate-500 dark:placeholder:text-neutral-400', false)
            ->assertSee('Kirim Pengajuan');
    }

    public function test_rejected_request_keeps_the_same_accessible_form(): void
    {
        $user = User::factory()->create(['role' => 'peserta']);
        OrganizerRequest::create(['user_id' => $user->id, 'status' => 'ditolak']);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-organizer-request-form', false)
            ->assertSee('bg-white dark:bg-deep text-slate-900 dark:text-paper', false)
            ->assertSee('for="organizer-alasan"', false)
            ->assertSee('for="organizer-kontak"', false)
            ->assertSee('Ajukan Ulang');
    }

    public function test_form_preserves_old_values_and_shows_validation_errors(): void
    {
        $user = User::factory()->create(['role' => 'peserta']);

        $this->actingAs($user)->from(route('dashboard'))
            ->post(route('organizer-request.store'), [
                'alasan' => 'Mengelola acara kampus',
                'kontak' => str_repeat('a', 256),
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('kontak');

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('value="Mengelola acara kampus"', false)
            ->assertSee('aria-invalid="true" aria-describedby="organizer-kontak-error"', false)
            ->assertSee('id="organizer-kontak-error"', false);
        $this->assertDatabaseCount('organizer_requests', 0);
    }

    public function test_request_submission_and_pending_state_still_work(): void
    {
        $user = User::factory()->create(['role' => 'peserta']);

        $this->actingAs($user)->from(route('dashboard'))
            ->post(route('organizer-request.store'), [
                'alasan' => 'Mengelola acara kampus',
                'kontak' => 'organizer@example.test',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('organizer_requests', [
            'user_id' => $user->id,
            'alasan' => 'Mengelola acara kampus',
            'kontak' => 'organizer@example.test',
            'status' => 'diajukan',
        ]);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pengajuanmu menunggu verifikasi admin.')
            ->assertDontSee('data-organizer-request-form', false);
    }
}

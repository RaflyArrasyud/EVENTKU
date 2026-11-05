<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeedDemoDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_opt_in(): void
    {
        config(['demo.enabled' => false, 'demo.password' => 'supersecret123']);

        $this->artisan('eventku:seed-demo')->assertSuccessful();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('events', 0);
    }

    public function test_requires_valid_secret_for_empty_init(): void
    {
        config(['demo.enabled' => true, 'demo.password' => 'short']);

        $this->artisan('eventku:seed-demo')->assertFailed();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('events', 0);
    }

    public function test_initializes_three_users_and_fifteen_events(): void
    {
        config(['demo.enabled' => true, 'demo.password' => 'supersecret123']);

        $this->artisan('eventku:seed-demo')
            ->assertSuccessful()
            ->expectsOutput('Demo dataset initialized: 3 users, 15 events.');

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('events', 15);
        $this->assertSame(
            ['admin', 'organizer', 'peserta'],
            User::orderBy('id')->pluck('role')->all()
        );
        foreach (User::all() as $user) {
            $this->assertTrue(Hash::check('supersecret123', $user->password));
            $this->assertNotNull($user->email_verified_at);
        }
    }

    public function test_repeat_run_never_resets_or_recreates(): void
    {
        config(['demo.enabled' => true, 'demo.password' => 'supersecret123']);
        $this->artisan('eventku:seed-demo')->assertSuccessful();

        $hashes = User::orderBy('id')->pluck('password')->all();
        $eventCount = Event::count();

        config(['demo.password' => 'anothersecret456']);
        $this->artisan('eventku:seed-demo')
            ->assertSuccessful()
            ->expectsOutput('Demo seed skipped: dataset already initialized.');

        $this->assertSame($hashes, User::orderBy('id')->pluck('password')->all());
        $this->assertSame($eventCount, Event::count());
        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('events', 15);
    }

    public function test_nonempty_dataset_is_noop_without_secret(): void
    {
        config(['demo.enabled' => true, 'demo.password' => 'supersecret123']);
        $this->artisan('eventku:seed-demo')->assertSuccessful();

        config(['demo.enabled' => false, 'demo.password' => null]);
        $this->artisan('eventku:seed-demo')->assertSuccessful();
        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('events', 15);
    }
}

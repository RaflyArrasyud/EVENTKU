<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\User;
use Database\Seeders\EventDemoSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SeedDemoData extends Command
{
    protected $signature = 'eventku:seed-demo';
    protected $description = 'Initialize deployment demo dataset once (opt-in, never resets on redeploy).';

    public function handle(EventDemoSeeder $seeder): int
    {
        $enabled = filter_var(config('demo.enabled'), FILTER_VALIDATE_BOOLEAN);
        if (! $enabled) {
            $this->line('Demo seed skipped: opt-in disabled.');

            return self::SUCCESS;
        }

        if (User::count() > 0 || Event::count() > 0) {
            $this->line('Demo seed skipped: dataset already initialized.');

            return self::SUCCESS;
        }

        $password = config('demo.password');
        if (! is_string($password) || strlen($password) < 12) {
            $this->error('Demo seed refused: EVENTKU_DEMO_PASSWORD must be at least 12 characters.');

            return self::FAILURE;
        }

        DB::transaction(function () use ($password, $seeder) {
            foreach ($this->accounts() as $account) {
                $user = User::create([
                    'name' => $account['name'],
                    'email' => $account['email'],
                    'password' => Hash::make($password),
                    'role' => $account['role'],
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
            }

            $seeder->run();
        });

        $this->info('Demo dataset initialized: 3 users, 15 events.');

        return self::SUCCESS;
    }

    private function accounts(): array
    {
        return [
            ['email' => 'admin@eventku.test', 'name' => 'Admin', 'role' => 'admin'],
            ['email' => 'organizer@eventku.test', 'name' => 'Organizer Demo', 'role' => 'organizer'],
            ['email' => 'peserta@eventku.test', 'name' => 'Peserta Demo', 'role' => 'peserta'],
        ];
    }
}

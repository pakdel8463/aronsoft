<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Dev-only superuser (admin@aron-soft.test / password).
 * Refuses to run outside the "local" environment.
 */
class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command?->warn('DemoUserSeeder skipped: only runs in the local environment.');

            return;
        }

        User::updateOrCreate(
            ['email' => 'admin@aron-soft.test'],
            [
                'name' => 'مدیر دمو',
                'password' => 'password', // hashed by the User model cast
                'is_superuser' => 1,
                'is_staff' => 1,
                'email_verified_at' => now(),
            ]
        );
    }
}

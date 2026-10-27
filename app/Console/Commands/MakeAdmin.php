<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MakeAdmin extends Command
{
    protected $signature = 'aron:make-admin {email} {--name=Admin} {--password= : Leave empty to generate one}';

    protected $description = 'Create (or promote) a superuser for the /panel admin area';

    public function handle(): int
    {
        $email = strtolower(trim($this->argument('email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email.');
            return self::FAILURE;
        }

        $password = $this->option('password') ?: Str::password(16, symbols: false);
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->forceFill(['is_superuser' => true, 'is_staff' => true])->save();
            $this->info("Existing user promoted to superuser: {$email}");
            if ($this->option('password')) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $this->info('Password updated.');
            }
            return self::SUCCESS;
        }

        $user = new User(['name' => $this->option('name'), 'email' => $email]);
        $user->forceFill([
            'password' => Hash::make($password),
            'is_superuser' => true,
            'is_staff' => true,
            'email_verified_at' => now(),
        ])->save();

        $this->info("Superuser created: {$email}");
        if (! $this->option('password')) {
            $this->warn("Generated password (shown once, change it after login): {$password}");
        }

        return self::SUCCESS;
    }
}

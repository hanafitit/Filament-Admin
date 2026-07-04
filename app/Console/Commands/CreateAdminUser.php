<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin
        {--name= : Admin display name}
        {--email= : Admin email}
        {--password= : Admin password}
        {--reset-password : Reset password if the user already exists}';

    protected $description = 'Create or update the first super admin account.';

    public function handle(): int
    {
        $name = $this->option('name') ?: env('ADMIN_NAME');
        $email = $this->option('email') ?: env('ADMIN_EMAIL');
        $password = $this->option('password') ?: env('ADMIN_PASSWORD');

        if (! $name && $this->input->isInteractive()) {
            $name = $this->ask('Admin name', 'Admin');
        }

        if (! $email && $this->input->isInteractive()) {
            $email = $this->ask('Admin email');
        }

        if (! $password && $this->input->isInteractive()) {
            $password = $this->secret('Admin password');
        }

        if (! $name) {
            $name = 'Admin';
        }

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->components->error('A valid admin email is required.');

            return self::FAILURE;
        }

        if (! $password || mb_strlen($password) < 8) {
            $this->components->error('Admin password must be at least 8 characters long.');

            return self::FAILURE;
        }

        Role::findOrCreate('super_admin');

        $user = User::query()->where('email', $email)->first();
        $shouldResetPassword = (bool) $this->option('reset-password');

        if (! $user) {
            $user = User::query()->create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            $this->components->info('Admin user created.');
        } else {
            $updates = [];

            if ($user->name !== $name) {
                $updates['name'] = $name;
            }

            if ($shouldResetPassword) {
                $updates['password'] = Hash::make($password);
            }

            if (is_null($user->email_verified_at)) {
                $updates['email_verified_at'] = now();
            }

            if ($updates !== []) {
                $user->forceFill($updates)->save();
            }

            $this->components->info('Admin user already exists. Role verified.');
        }

        $user->syncRoles(['super_admin']);

        $this->line("Email: {$user->email}");
        $this->line('Role: super_admin');

        return self::SUCCESS;
    }
}

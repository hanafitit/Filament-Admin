<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateAdminUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_super_admin_user(): void
    {
        $this->artisan('app:create-admin', [
            '--name' => 'Owner',
            '--email' => 'owner@example.com',
            '--password' => 'secret123',
        ])->assertSuccessful();

        $user = User::query()->where('email', 'owner@example.com')->first();

        $this->assertNotNull($user);
        $this->assertSame('Owner', $user->name);
        $this->assertTrue($user->hasRole('super_admin'));
        $this->assertDatabaseHas('roles', ['name' => 'super_admin']);
    }

    public function test_it_keeps_existing_password_without_reset_option(): void
    {
        Role::findOrCreate('super_admin');

        $user = User::query()->create([
            'name' => 'Existing Admin',
            'email' => 'owner@example.com',
            'password' => 'old-password',
        ]);

        $originalHash = $user->password;

        $this->artisan('app:create-admin', [
            '--name' => 'Updated Admin',
            '--email' => 'owner@example.com',
            '--password' => 'secret123',
        ])->assertSuccessful();

        $user->refresh();

        $this->assertSame('Updated Admin', $user->name);
        $this->assertSame($originalHash, $user->password);
        $this->assertTrue($user->hasRole('super_admin'));
    }
}

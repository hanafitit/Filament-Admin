<?php

namespace Tests\Feature;

use App\Models\User;
use App\Filament\Pages\ResponsesAndApplications;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

class ResponsesAndApplicationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_page_component_renders(): void
    {
        Role::findOrCreate('super_admin');
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user);

        Livewire::test(ResponsesAndApplications::class)
            ->assertStatus(200);
    }
}

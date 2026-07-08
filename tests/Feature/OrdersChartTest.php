<?php

namespace Tests\Feature;

use App\Filament\Widgets\OrdersChart;
use App\Models\Order;
use App\Models\Source;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OrdersChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_render_orders_chart_widget(): void
    {
        $user = User::factory()->create();
        $source = Source::create(['name' => 'Test Source', 'slug' => 'test-source']);
        $status = Status::create(['name' => 'Test Status', 'slug' => 'test-status', 'color' => 'gray', 'is_default' => true]);

        Order::create([
            'title' => 'Test Order',
            'budget' => 100,
            'source_id' => $source->id,
            'status_id' => $status->id,
            'user_id' => $user->id,
            'deadline' => now()->addDay(),
        ]);

        Livewire::actingAs($user)
            ->test(OrdersChart::class)
            ->assertStatus(200)
            ->assertSee('Продажи за последнюю неделю');
    }
}

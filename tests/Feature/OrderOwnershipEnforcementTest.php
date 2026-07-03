<?php

namespace Tests\Feature;

use App\Filament\Pages\OrderKanban;
use App\Filament\Resources\OrderResource\Pages\CreateOrder;
use App\Filament\Resources\OrderResource\Pages\EditOrder;
use App\Filament\Resources\SourceResource;
use App\Filament\Resources\StatusResource;
use App\Models\Order;
use App\Models\Source;
use App\Models\Status;
use App\Models\User;
use Livewire\Livewire;
use ReflectionMethod;
use ReflectionProperty;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderOwnershipEnforcementTest extends TestCase
{
    public function test_manager_can_assign_order_to_executor_on_create(): void
    {
        Role::findOrCreate('executor');
        Role::findOrCreate('manager');
        $manager = User::factory()->create();
        $manager->assignRole('manager');
        $executor = User::factory()->create();
        $executor->assignRole('executor');

        $this->actingAs($manager);

        $page = app(CreateOrder::class);
        $method = new ReflectionMethod($page, 'mutateFormDataBeforeCreate');
        $method->setAccessible(true);

        $result = $method->invoke($page, ['user_id' => $executor->id]);

        $this->assertSame($executor->id, $result['user_id']);
        $this->assertSame($manager->id, $result['manager_id']);
    }

    public function test_executor_cannot_assign_order_to_another_user_on_create(): void
    {
        Role::findOrCreate('executor');
        $executor = User::factory()->create();
        $executor->assignRole('executor');
        $otherUser = User::factory()->create();

        $this->actingAs($executor);

        $page = app(CreateOrder::class);
        $method = new ReflectionMethod($page, 'mutateFormDataBeforeCreate');
        $method->setAccessible(true);

        $result = $method->invoke($page, ['user_id' => $otherUser->id]);

        $this->assertSame($executor->id, $result['user_id']);
    }

    public function test_manager_can_reassign_order_owner_on_edit(): void
    {
        Role::findOrCreate('executor');
        Role::findOrCreate('manager');
        [$source, $status] = $this->createCatalog();
        $manager = User::factory()->create();
        $manager->assignRole('manager');
        $originalOwner = User::factory()->create();
        $originalOwner->assignRole('executor');
        $otherUser = User::factory()->create();
        $otherUser->assignRole('executor');
        $order = $this->makeOrder($source, $status, $originalOwner);

        $this->actingAs($manager);

        $page = app(EditOrder::class);
        $property = new ReflectionProperty($page, 'record');
        $property->setAccessible(true);
        $property->setValue($page, $order);

        $method = new ReflectionMethod($page, 'mutateFormDataBeforeSave');
        $method->setAccessible(true);

        $result = $method->invoke($page, ['user_id' => $otherUser->id]);

        $this->assertSame($otherUser->id, $result['user_id']);
        $this->assertSame($manager->id, $result['manager_id']);
    }

    public function test_executor_cannot_reassign_order_owner_on_edit(): void
    {
        Role::findOrCreate('executor');
        [$source, $status] = $this->createCatalog();
        $executor = User::factory()->create();
        $executor->assignRole('executor');
        $otherUser = User::factory()->create();
        $order = $this->makeOrder($source, $status, $executor);

        $this->actingAs($executor);

        $page = app(EditOrder::class);
        $property = new ReflectionProperty($page, 'record');
        $property->setAccessible(true);
        $property->setValue($page, $order);

        $method = new ReflectionMethod($page, 'mutateFormDataBeforeSave');
        $method->setAccessible(true);

        $result = $method->invoke($page, ['user_id' => $otherUser->id]);

        $this->assertSame($executor->id, $result['user_id']);
    }

    public function test_executor_cannot_move_other_users_order_from_kanban(): void
    {
        Role::findOrCreate('executor');
        [$source, $fromStatus, $toStatus] = $this->createCatalogWithSecondStatus();
        $executor = User::factory()->create();
        $executor->assignRole('executor');
        $otherUser = User::factory()->create();
        $ownOrder = $this->makeOrder($source, $fromStatus, $executor);
        $otherOrder = $this->makeOrder($source, $fromStatus, $otherUser, ['title' => 'Foreign']);

        $this->actingAs($executor);

        Livewire::test(OrderKanban::class)
            ->call('moveOrder', $ownOrder->id, $toStatus->id)
            ->call('moveOrder', $otherOrder->id, $toStatus->id);

        $this->assertSame($toStatus->id, $ownOrder->fresh()->status_id);
        $this->assertSame($fromStatus->id, $otherOrder->fresh()->status_id);
    }

    public function test_executor_cannot_delete_orders_from_kanban(): void
    {
        Role::findOrCreate('executor');
        [$source, $status] = $this->createCatalog();
        $executor = User::factory()->create();
        $executor->assignRole('executor');
        $order = $this->makeOrder($source, $status, $executor);

        $this->actingAs($executor);

        Livewire::test(OrderKanban::class)
            ->call('deleteOrder', $order->id);

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_executor_cannot_access_catalog_resources(): void
    {
        Role::findOrCreate('executor');
        $executor = User::factory()->create();
        $executor->assignRole('executor');

        $this->actingAs($executor);

        $this->assertFalse(SourceResource::canAccess());
        $this->assertFalse(StatusResource::canAccess());
    }

    protected function createCatalog(): array
    {
        $source = Source::query()->create([
            'name' => 'Website',
            'slug' => 'website',
        ]);

        $status = Status::query()->create([
            'name' => 'Новый',
            'color' => 'gray',
            'sort_order' => 1,
        ]);

        return [$source, $status];
    }

    protected function createCatalogWithSecondStatus(): array
    {
        [$source, $fromStatus] = $this->createCatalog();
        $toStatus = Status::query()->create([
            'name' => 'В работе',
            'color' => 'warning',
            'sort_order' => 2,
        ]);

        return [$source, $fromStatus, $toStatus];
    }

    protected function makeOrder(Source $source, Status $status, User $user, array $overrides = []): Order
    {
        return Order::query()->create(array_merge([
            'title' => 'Test order',
            'description' => 'Description',
            'source_id' => $source->id,
            'status_id' => $status->id,
            'user_id' => $user->id,
            'budget' => 1000,
            'commission' => 100,
            'deadline' => now()->addDay(),
            'payment_deadline' => now()->addDays(2),
        ], $overrides));
    }
}

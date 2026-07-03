<?php

namespace Tests\Feature;

use App\Exports\OrdersExport;
use App\Models\Order;
use App\Models\Source;
use App\Models\Status;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrdersExportTest extends TestCase
{
    public function test_guest_export_query_returns_no_orders(): void
    {
        [$source, $status] = $this->createCatalog();
        $order = $this->makeOrder($source, $status, User::factory()->create());

        $export = new OrdersExport(null, null, null, false);

        $this->assertSame([], $export->query()->pluck('id')->all());
        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }

    public function test_executor_export_is_limited_to_owned_orders(): void
    {
        Role::findOrCreate('executor');
        [$source, $status] = $this->createCatalog();
        $executor = User::factory()->create();
        $executor->assignRole('executor');
        $otherUser = User::factory()->create();

        $ownOrder = $this->makeOrder($source, $status, $executor, ['title' => 'Own']);
        $otherOrder = $this->makeOrder($source, $status, $otherUser, ['title' => 'Other']);

        $export = new OrdersExport(null, null, $executor->id, true);

        $this->assertSame([$ownOrder->id], $export->query()->pluck('id')->all());
        $this->assertNotContains($otherOrder->id, $export->query()->pluck('id')->all());
    }

    public function test_manager_export_can_include_all_orders(): void
    {
        Role::findOrCreate('manager');
        [$source, $status] = $this->createCatalog();
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $first = $this->makeOrder($source, $status, User::factory()->create(), ['title' => 'First']);
        $second = $this->makeOrder($source, $status, User::factory()->create(), ['title' => 'Second']);

        $export = new OrdersExport(null, null, $manager->id, false);

        $this->assertEqualsCanonicalizing([$first->id, $second->id], $export->query()->pluck('id')->all());
    }

    public function test_export_escapes_formula_like_values(): void
    {
        [$source, $status] = $this->createCatalog();
        $user = User::factory()->create(['name' => '@admin']);
        $order = $this->makeOrder($source, $status, $user, ['title' => '=cmd']);
        $order->setRelation('source', Source::query()->create(['name' => '+source', 'slug' => 'source-safe']));
        $order->setRelation('user', $user);

        $export = new OrdersExport(null, null, $user->id, false);
        $row = $export->map($order);

        $this->assertSame("'=cmd", $row[1]);
        $this->assertSame("'+source", $row[2]);
        $this->assertSame("'@admin", $row[4]);
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

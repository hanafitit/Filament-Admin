<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Source;
use App\Models\Status;
use App\Models\User;
use App\Services\MarketplaceCommissionCalculator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MarketplaceCommissionTest extends TestCase
{
    #[DataProvider('commissionCases')]
    public function test_calculates_marketplace_commission_by_source_and_budget(string $sourceSlug, float $budget, float $expected): void
    {
        $this->assertSame($expected, MarketplaceCommissionCalculator::calculateForSourceSlug($sourceSlug, $budget));
    }

    public function test_order_commission_is_recalculated_when_saved_for_marketplace_source(): void
    {
        $source = Source::query()->create([
            'name' => 'Kwork',
            'slug' => 'kwork',
        ]);
        $status = Status::query()->create([
            'name' => 'Новый',
            'color' => 'primary',
            'sort_order' => 1,
        ]);
        $user = User::factory()->create();

        $order = Order::query()->create([
            'title' => 'Marketplace order',
            'source_id' => $source->id,
            'status_id' => $status->id,
            'user_id' => $user->id,
            'budget' => 45000,
            'commission' => 1,
            'deadline' => now()->addDay(),
        ]);

        $this->assertSame(5400.0, (float) $order->fresh()->commission);
    }

    /**
     * @return array<string, array{0: string, 1: float, 2: float}>
     */
    public static function commissionCases(): array
    {
        return [
            'kwork low tier' => ['kwork', 30000, 6000],
            'kwork middle tier' => ['kwork', 45000, 5400],
            'kwork high tier' => ['kwork', 400000, 30000],
            'fl low tier' => ['fl', 35000, 7000],
            'fl middle tier' => ['fl', 120000, 14400],
            'fl high tier' => ['fl', 400000, 28000],
        ];
    }
}

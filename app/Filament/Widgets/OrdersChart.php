<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrdersChart extends Widget
{
    protected static string $view = 'filament.widgets.orders-chart';

    protected int | string | array $columnSpan = 'full';

    public function getData(): array
    {
        $days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $days->put(Carbon::now()->subDays($i)->format('Y-m-d'), 0);
        }

        $orders = Order::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(budget) as total_amount')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total_amount', 'date');

        $data = $days->merge($orders);

        return [
            'labels' => $data->keys()->map(fn ($date) => Carbon::parse($date)->format('d.m'))->toArray(),
            'values' => $data->values()->toArray(),
        ];
    }
}

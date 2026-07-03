<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Status;

class ReportService
{
    public function getFinanceReport(?string $startDate, ?string $endDate): array
    {
        $paidStatusId = Status::query()
            ->where('name', 'Оплачен')
            ->value('id');

        if (! $paidStatusId) {
            return [
                'total_budget' => 0,
                'total_commission' => 0,
                'total_net_income' => 0,
                'by_source' => [],
                'by_user' => [],
            ];
        }

        $query = Order::query()
            ->where('status_id', $paidStatusId);

        if ($startDate) {
            $query->whereDate('orders.updated_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('orders.updated_at', '<=', $endDate);
        }

        return [
            'total_budget' => (float) (clone $query)->sum('budget'),
            'total_commission' => (float) (clone $query)->sum('commission'),
            'total_net_income' => (float) (clone $query)->sum('net_income'),
            'by_source' => (clone $query)
                ->leftJoin('sources', 'orders.source_id', '=', 'sources.id')
                ->selectRaw("COALESCE(sources.name, 'Без источника') as name, SUM(orders.net_income) as income")
                ->groupBy('sources.name')
                ->orderByDesc('income')
                ->get()
                ->map(fn (object $row): array => [
                    'name' => $row->name,
                    'income' => (float) $row->income,
                ])
                ->all(),
            'by_user' => (clone $query)
                ->leftJoin('users', 'orders.user_id', '=', 'users.id')
                ->selectRaw("COALESCE(users.name, 'Не назначен') as name, SUM(orders.net_income) as income")
                ->groupBy('users.name')
                ->orderByDesc('income')
                ->get()
                ->map(fn (object $row): array => [
                    'name' => $row->name,
                    'income' => (float) $row->income,
                ])
                ->all(),
        ];
    }
}

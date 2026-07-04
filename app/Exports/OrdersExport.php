<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(
        protected ?string $startDate,
        protected ?string $endDate,
    ) {}

    public function query(): Builder
    {
        $query = Order::query()->with(['source', 'status', 'user']);
        $user = Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('executor') && ! $user->hasRole('super_admin')) {
            $query->where('user_id', $user->getAuthIdentifier());
        }

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }

        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        return $query->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Название',
            'Источник',
            'Статус',
            'Исполнитель',
            'Бюджет',
            'Комиссия',
            'Чистый доход',
            'Дата',
            'Дедлайн',
            'Дедлайн оплаты',
        ];
    }

    public function map($order): array
    {
        return [
            $order->id,
            $this->escapeSpreadsheetValue($order->title),
            $this->escapeSpreadsheetValue($order->source?->name ?? 'Без источника'),
            $this->escapeSpreadsheetValue($order->status?->name ?? 'Без статуса'),
            $this->escapeSpreadsheetValue($order->user?->name ?? 'Не назначен'),
            $order->budget,
            $order->commission,
            $order->net_income,
            $order->created_at?->format('d.m.Y'),
            $order->deadline?->format('d.m.Y H:i'),
            $order->payment_deadline?->format('d.m.Y H:i'),
        ];
    }

    protected function escapeSpreadsheetValue(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return preg_match('/^[=\-+@]/', $value) === 1
            ? "'".$value
            : $value;
    }
}

<?php

namespace App\Filament\Widgets;

use App\Services\ReportService;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FinancialReportStats extends StatsOverviewWidget
{
    public array $report = [];

    public function mount(ReportService $reportService): void
    {
        if ($this->report !== []) {
            return;
        }

        $this->report = $reportService->getFinanceReport(null, null);
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Общий бюджет', $this->formatMoney($this->report['total_budget'] ?? 0))
                ->description('Сумма оплаченных заказов')
                ->color('primary'),
            Stat::make('Комиссия бирж', $this->formatMoney($this->report['total_commission'] ?? 0))
                ->description('Удержания по заказам')
                ->color('warning'),
            Stat::make('Чистый доход', $this->formatMoney($this->report['total_net_income'] ?? 0))
                ->description('После вычета комиссии')
                ->color('success'),
        ];
    }

    protected function formatMoney(float|int $amount): string
    {
        return number_format((float) $amount, 2, ',', ' ').' ₽';
    }
}

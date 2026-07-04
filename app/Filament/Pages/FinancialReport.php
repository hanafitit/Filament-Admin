<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FinancialReportStats;
use App\Services\ReportService;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Widgets\WidgetConfiguration;

class FinancialReport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Финансовый отчет';

    protected static ?string $title = 'Финансовый отчет';

    protected static ?string $navigationGroup = 'Аналитика';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.financial-report';

    public ?array $filters = [];

    public array $report = [];

    public function mount(ReportService $reportService): void
    {
        $this->form->fill([
            'start_date' => null,
            'end_date' => null,
        ]);

        $this->loadReport($reportService);
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'manager']) ?? false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Период отчета')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Дата от'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Дата до'),
                    ])
                    ->columns(2),
            ])
            ->statePath('filters');
    }

    public function applyFilters(ReportService $reportService): void
    {
        $this->loadReport($reportService);
    }

    protected function loadReport(ReportService $reportService): void
    {
        $this->report = $reportService->getFinanceReport(
            $this->filters['start_date'] ?? null,
            $this->filters['end_date'] ?? null,
        );
    }

    /**
     * @return array<class-string|WidgetConfiguration>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            FinancialReportStats::make([
                'report' => $this->report,
            ]),
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 3;
    }

    public function formatMoney(float|int $amount): string
    {
        return number_format((float) $amount, 2, ',', ' ').' ₽';
    }
    public function getOrdersPageUrl(): string
    {
        return route('filament.admin.resources.orders.index');
    }
}

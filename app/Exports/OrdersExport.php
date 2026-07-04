<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\AfterSheet;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OrdersExport implements FromQuery, ShouldAutoSize, WithEvents, WithHeadings, WithMapping
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $highestColumn = $sheet->getHighestColumn();
                $highestRow = $sheet->getHighestRow();
                $headerRange = "A1:{$highestColumn}1";
                $dataRange = "A1:{$highestColumn}{$highestRow}";

                $sheet->freezePane('A2');
                $sheet->setAutoFilter($dataRange);

                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1F4E78'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getStyle($dataRange)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle("A2:{$highestColumn}{$highestRow}")->getAlignment()->setWrapText(true);
            },
        ];
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\Status;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OrderKanban extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationLabel = 'Канбан';

    protected static ?string $title = 'Канбан-доска заказов';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.order-kanban';

    protected ?string $maxContentWidth = MaxWidth::Full->value;

    protected ?Collection $statuses = null;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createOrder')
                ->label('Новый заказ')
                ->url(route('filament.admin.resources.orders.create'))
                ->icon('heroicon-o-plus'),
        ];
    }

    public function moveOrder(int $orderId, int $statusId): void
    {
        $order = $this->getVisibleOrders()->find($orderId);
        $status = $this->getStatuses()->firstWhere('id', $statusId);

        if (! $order || ! $status) {
            return;
        }

        $order->update([
            'status_id' => $status->id,
        ]);
    }

    public function deleteOrder(int $orderId): void
    {
        if (! $this->canDeleteOrders()) {
            return;
        }

        $order = $this->getVisibleOrders()->find($orderId);

        if (! $order) {
            return;
        }

        $order->delete();
    }

    public function getBoardProperty(): array
    {
        $ordersByStatus = $this->getVisibleOrders()
            ->orderBy('deadline')
            ->get()
            ->groupBy('status_id');

        return $this->getStatuses()
            ->map(function (Status $status) use ($ordersByStatus): array {
                $orders = $ordersByStatus
                    ->get($status->id, collect())
                    ->map(fn (Order $order): array => $this->formatOrderCard($order))
                    ->values()
                    ->all();

                return [
                    'id' => $status->id,
                    'title' => $status->name,
                    'color' => $status->color,
                    'orders' => $orders,
                ];
            })
            ->all();
    }

    protected function getStatuses(): Collection
    {
        return $this->statuses ??= Status::query()
            ->workflow()
            ->orderBy('sort_order')
            ->get();
    }

    protected function getVisibleOrders(): Builder
    {
        $query = Order::query();

        if ($this->shouldRestrictToOwnOrders()) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    protected function shouldRestrictToOwnOrders(): bool
    {
        return auth()->user()?->hasRole('executor') && ! auth()->user()?->hasRole('super_admin');
    }

    protected function canSeeBudget(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'manager']) ?? false;
    }

    public function canDeleteOrders(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'manager']) ?? false;
    }

    protected function formatOrderCard(Order $order): array
    {
        $metaLines = [];

        if ($this->canSeeBudget()) {
            $budget = number_format((float) $order->budget, 2, '.', ' ');
            $metaLines[] = "Бюджет: {$budget} ₽";
        }

        $metaLines[] = 'Дедлайн: '.$order->deadline?->format('d.m');

        return [
            'id' => $order->id,
            'title' => $this->wrapTitleAfterSecondWord($order->title),
            'status' => $order->status_id,
            'meta_lines' => $metaLines,
            'can_delete' => $this->canDeleteOrders(),
        ];
    }

    protected function wrapTitleAfterSecondWord(string $title): string
    {
        $words = preg_split('/\s+/u', trim($title), -1, PREG_SPLIT_NO_EMPTY);

        if (! $words || count($words) <= 2) {
            return $title;
        }

        return implode(' ', array_slice($words, 0, 2))."\n".implode(' ', array_slice($words, 2));
    }
}

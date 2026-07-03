<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! OrderResource::canAssignExecutor()) {
            $data['user_id'] = $this->record->user_id ?: auth()->id();
        }

        if (blank($data['manager_id'] ?? null) && OrderResource::canAssignExecutor()) {
            $data['manager_id'] = $this->record->manager_id ?: auth()->id();
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => OrderResource::canDeleteOrders()),
        ];
    }
}

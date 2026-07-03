<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! OrderResource::canAssignExecutor()) {
            $data['user_id'] = auth()->id();
        }

        if (blank($data['manager_id'] ?? null) && OrderResource::canAssignExecutor()) {
            $data['manager_id'] = auth()->id();
        }

        return $data;
    }
}

<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ResponsesAndApplications extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Отклики и заявки';

    protected static ?string $title = 'Отклики и заявки';

    protected static string $view = 'filament.pages.responses-and-applications';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'manager']) ?? false;
    }
}

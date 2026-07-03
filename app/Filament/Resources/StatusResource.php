<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusResource\Pages;
use App\Models\Status;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StatusResource extends Resource
{
    protected static ?string $model = Status::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Статус';

    protected static ?string $pluralModelLabel = 'Статусы';

    protected static ?string $navigationLabel = 'Статусы';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'manager']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Название'),
                Forms\Components\TextInput::make('color')
                    ->maxLength(255)
                    ->label('Цвет'),
                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->label('Порядок'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Название'),
                Tables\Columns\TextColumn::make('color')
                    ->badge()
                    ->label('Цвет'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->sortable()
                    ->label('Порядок'),
            ])
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageStatuses::route('/'),
        ];
    }
}

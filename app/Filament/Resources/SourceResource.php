<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SourceResource\Pages;
use App\Models\Source;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SourceResource extends Resource
{
    protected static ?string $model = Source::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Источник';

    protected static ?string $pluralModelLabel = 'Источники';

    protected static ?string $navigationLabel = 'Источники';

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
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->label('Код'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Название'),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->label('Код'),
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
            'index' => Pages\ManageSources::route('/'),
        ];
    }
}

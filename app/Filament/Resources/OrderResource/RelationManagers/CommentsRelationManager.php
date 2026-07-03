<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\OrderComment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Внутренние заметки и комментарии';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'manager', 'executor']) ?? false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\ToggleButtons::make('type')
                    ->label('Тип')
                    ->options(static::getTypeOptions())
                    ->icons([
                        OrderComment::TYPE_COMMENT => 'heroicon-o-chat-bubble-left-right',
                        OrderComment::TYPE_INTERNAL_NOTE => 'heroicon-o-lock-closed',
                    ])
                    ->colors([
                        OrderComment::TYPE_COMMENT => 'info',
                        OrderComment::TYPE_INTERNAL_NOTE => 'warning',
                    ])
                    ->inline()
                    ->default(OrderComment::TYPE_COMMENT)
                    ->required(),
                Forms\Components\Hidden::make('is_internal')
                    ->default(true)
                    ->dehydrated(),
                Forms\Components\Textarea::make('content')
                    ->label('Текст')
                    ->required()
                    ->rows(3),
            ])
            ->columns(1);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::getTypeOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        OrderComment::TYPE_INTERNAL_NOTE => 'warning',
                        default => 'info',
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Автор')
                    ->weight(FontWeight::Bold),
                Tables\Columns\TextColumn::make('content')
                    ->label('Содержимое')
                    ->wrap(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Добавлен')
                    ->dateTime('d.m.Y H:i'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Добавить запись')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();
                        $data['is_internal'] = true;

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['is_internal'] = true;

                        return $data;
                    })
                    ->visible(fn ($record): bool => $record->user_id === auth()->id() || (auth()->user()?->hasRole('super_admin') ?? false)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record): bool => $record->user_id === auth()->id() || (auth()->user()?->hasRole('super_admin') ?? false)),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options(static::getTypeOptions()),
            ])
            ->bulkActions([]);
    }

    /**
     * @return array<string, string>
     */
    protected static function getTypeOptions(): array
    {
        return [
            OrderComment::TYPE_COMMENT => 'Комментарий',
            OrderComment::TYPE_INTERNAL_NOTE => 'Внутренняя заметка',
        ];
    }
}

<?php

namespace App\Filament\Resources;

use App\Exports\OrdersExport;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Source;
use App\Models\Status;
use App\Models\User;
use App\Services\MarketplaceCommissionCalculator;
use App\Support\Uploads\OrderAttachmentUpload;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?array $sourceOptions = null;

    protected static ?array $statusOptions = null;

    protected static ?array $userOptions = null;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Заказ';

    protected static ?string $pluralModelLabel = 'Заказы';

    protected static ?string $navigationLabel = 'Заказы';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основная информация')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->label('Название заказа'),
                        Forms\Components\Textarea::make('description')
                            ->rows(4)
                            ->columnSpanFull()
                            ->label('Описание'),
                        Forms\Components\Select::make('source_id')
                            ->options(static::getSourceOptions())
                            ->native(true)
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set, Get $get): mixed => $set(
                                'commission',
                                static::calculateCommission($get('source_id'), $get('budget')),
                            ))
                            ->label('Источник'),
                        Forms\Components\Select::make('status_id')
                            ->options(static::getStatusOptions())
                            ->native(true)
                            ->required()
                            ->label('Статус'),
                        Forms\Components\Select::make('user_id')
                            ->options(static::getUserOptions())
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->required(fn (): bool => static::canAssignExecutor())
                            ->label('Исполнитель')
                            ->visible(fn (): bool => static::canAssignExecutor()),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Финансы и сроки')
                    ->schema([
                        Forms\Components\TextInput::make('budget')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->prefix('₽')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, Get $get): mixed => $set(
                                'commission',
                                static::calculateCommission($get('source_id'), $get('budget')),
                            ))
                            ->label('Бюджет'),
                        Forms\Components\TextInput::make('commission')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(fn (callable $get): ?float => filled($get('budget')) ? (float) $get('budget') : null)
                            ->prefix('₽')
                            ->readOnly()
                            ->dehydrated()
                            ->helperText('Рассчитывается автоматически для Kwork и FL')
                            ->label('Комиссия биржи'),
                        Forms\Components\DateTimePicker::make('deadline')
                            ->required()
                            ->seconds(false)
                            ->label('Дедлайн'),
                        Forms\Components\DateTimePicker::make('payment_deadline')
                            ->seconds(false)
                            ->label('Дедлайн оплаты'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Документация и файлы проекта')
                    ->schema([
                        Forms\Components\Repeater::make('attachments')
                            ->relationship('attachments')
                            ->label('Прикрепить ТЗ / Макеты / Результаты')
                            ->defaultItems(0)
                            ->itemLabel(fn (array $state): ?string => $state['file_name'] ?? 'Файл')
                            ->schema([
                                Forms\Components\FileUpload::make('file_path')
                                    ->label('Файл')
                                    ->disk('local')
                                    ->directory('order-attachments')
                                    ->visibility('private')
                                    ->acceptedFileTypes(OrderAttachmentUpload::acceptedFileTypes())
                                    ->maxSize(OrderAttachmentUpload::effectiveMaxKilobytes())
                                    ->helperText(OrderAttachmentUpload::helperText())
                                    ->validationMessages([
                                        'max' => OrderAttachmentUpload::uploadTooLargeMessage(),
                                        'mimetypes' => OrderAttachmentUpload::invalidTypeMessage(),
                                        'mimes' => OrderAttachmentUpload::invalidTypeMessage(),
                                    ])
                                    ->storeFileNamesIn('file_name')
                                    ->downloadable()
                                    ->openable()
                                    ->required(),
                                Forms\Components\Hidden::make('file_name'),
                                Forms\Components\Hidden::make('file_size'),
                            ])
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['file_size'] = filled($data['file_path'])
                                    ? Storage::disk('local')->size($data['file_path'])
                                    : 0;

                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                $data['file_size'] = filled($data['file_path'])
                                    ? Storage::disk('local')->size($data['file_path'])
                                    : 0;

                                return $data;
                            })
                            ->columns(1)
                            ->addActionLabel('Добавить файл')
                            ->reorderable(false)
                            ->collapsible(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->label('Название'),
                Tables\Columns\TextColumn::make('source.name')
                    ->label('Источник'),
                Tables\Columns\TextColumn::make('status.name')
                    ->badge()
                    ->color(fn (Order $record): string => $record->status?->color ?: 'gray')
                    ->label('Статус'),
                Tables\Columns\TextColumn::make('budget')
                    ->money('RUB')
                    ->visible(fn (): bool => static::canSeeFinancials())
                    ->label('Бюджет'),
                Tables\Columns\TextColumn::make('net_income')
                    ->money('RUB')
                    ->visible(fn (): bool => static::canSeeFinancials())
                    ->label('Чистый доход'),
                Tables\Columns\TextColumn::make('deadline')
                    ->dateTime()
                    ->sortable()
                    ->label('Дедлайн'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_id')
                    ->options(static::getStatusOptions())
                    ->label('Статус'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Экспорт')
                    ->icon('heroicon-o-document-arrow-down')
                    ->visible(fn (): bool => static::canSeeFinancials())
                    ->form([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Дата от'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Дата до'),
                        Forms\Components\Select::make('format')
                            ->label('Формат')
                            ->options([
                                'xlsx' => 'Excel (.xlsx)',
                                'csv' => 'CSV (.csv)',
                            ])
                            ->default('xlsx')
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $user = auth()->user();
                        $format = $data['format'] ?? 'xlsx';
                        $writerType = $format === 'csv'
                            ? ExcelFormat::CSV
                            : ExcelFormat::XLSX;

                        return Excel::download(
                            new OrdersExport(
                                $data['start_date'] ?? null,
                                $data['end_date'] ?? null,
                                $user?->getAuthIdentifier(),
                                (bool) ($user?->hasRole('executor') && ! $user?->hasRole('super_admin')),
                            ),
                            "orders.{$format}",
                            $writerType,
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => static::canDeleteOrders()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CommentsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['source', 'status']);

        if (auth()->user()?->hasRole('executor') && ! auth()->user()?->hasRole('super_admin')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    protected static function getSourceOptions(): array
    {
        return static::$sourceOptions ??= Source::query()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected static function getStatusOptions(): array
    {
        return static::$statusOptions ??= Status::query()
            ->workflow()
            ->orderBy('sort_order')
            ->pluck('name', 'id')
            ->all();
    }

    protected static function getUserOptions(): array
    {
        return static::$userOptions ??= User::query()
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('name', ['executor', 'manager', 'super_admin']))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected static function calculateCommission(mixed $sourceId, mixed $budget): float
    {
        return static::calculateMarketplaceCommission($sourceId, $budget) ?? 0.0;
    }

    protected static function calculateMarketplaceCommission(mixed $sourceId, mixed $budget): ?float
    {
        return MarketplaceCommissionCalculator::calculateForSourceId($sourceId, $budget);
    }

    public static function canAssignExecutor(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'manager']) ?? false;
    }

    public static function canSeeFinancials(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'manager']) ?? false;
    }

    public static function canDeleteOrders(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'manager']) ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}

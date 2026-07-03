<?php

namespace App\Models;

use App\Services\MarketplaceCommissionCalculator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'title',
    'description',
    'source_id',
    'status_id',
    'user_id',
    'manager_id',
    'budget',
    'commission',
    'deadline',
    'payment_deadline',
])]
class Order extends Model
{
    protected static function booted(): void
    {
        static::saving(function (Order $order): void {
            $commission = MarketplaceCommissionCalculator::calculateForOrder($order);

            if ($commission !== null) {
                $order->commission = $commission;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
            'commission' => 'decimal:2',
            'net_income' => 'decimal:2',
            'deadline' => 'datetime',
            'payment_deadline' => 'datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(OrderComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(OrderAttachment::class);
    }
}

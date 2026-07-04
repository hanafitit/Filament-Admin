<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'color', 'sort_order'])]
class Status extends Model
{
    public const WORKFLOW_NAMES = [
        'Новый',
        'В работе/на проверке',
        'Сдан',
        'Оплачен',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeWorkflow(Builder $query): Builder
    {
        return $query->whereIn('name', self::WORKFLOW_NAMES);
    }
}

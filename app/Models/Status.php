<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'color', 'sort_order'])]
class Status extends Model
{
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}

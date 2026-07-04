<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug'])]
class Source extends Model
{
    public static function generateSlugFromName(?string $name): string
    {
        $normalized = Str::lower(trim((string) $name));

        return match ($normalized) {
            'fl.ru', 'fl ru', 'fl' => 'fl',
            'kwork' => 'kwork',
            'прямой клиент', 'прямой-клиент', 'direct', 'direct client' => 'direct',
            default => Str::slug($normalized),
        };
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}

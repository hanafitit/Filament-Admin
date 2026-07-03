<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'user_id', 'type', 'content', 'is_internal'])]
class OrderComment extends Model
{
    public const TYPE_COMMENT = 'comment';

    public const TYPE_INTERNAL_NOTE = 'internal_note';

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

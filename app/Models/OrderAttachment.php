<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['order_id', 'file_path', 'file_name', 'file_size'])]
class OrderAttachment extends Model
{
    protected static function booted(): void
    {
        static::deleted(function (OrderAttachment $attachment): void {
            if ($attachment->file_path) {
                Storage::disk('local')->delete($attachment->file_path);
            }
        });

        static::updating(function (OrderAttachment $attachment): void {
            if (! $attachment->isDirty('file_path')) {
                return;
            }

            $originalPath = $attachment->getOriginal('file_path');

            if ($originalPath && $originalPath !== $attachment->file_path) {
                Storage::disk('local')->delete($originalPath);
            }
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

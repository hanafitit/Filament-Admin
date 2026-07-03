<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Source;

class MarketplaceCommissionCalculator
{
    public static function calculateForOrder(Order $order): ?float
    {
        if (blank($order->source_id)) {
            return null;
        }

        $sourceSlug = $order->relationLoaded('source') && (string) $order->source?->getKey() === (string) $order->source_id
            ? $order->source?->slug
            : Source::query()->whereKey($order->source_id)->value('slug');

        return static::calculateForSourceSlug($sourceSlug, $order->budget);
    }

    public static function calculateForSourceId(mixed $sourceId, mixed $budget): ?float
    {
        if (blank($sourceId)) {
            return null;
        }

        $sourceSlug = Source::query()
            ->whereKey($sourceId)
            ->value('slug');

        return static::calculateForSourceSlug($sourceSlug, $budget);
    }

    public static function calculateForSourceSlug(?string $sourceSlug, mixed $budget): ?float
    {
        $budget = static::normalizeBudget($budget);

        if ($budget === null) {
            return null;
        }

        $rate = match ($sourceSlug) {
            'kwork' => static::kworkRate($budget),
            'fl' => static::flRate($budget),
            default => null,
        };

        return $rate === null
            ? null
            : round($budget * $rate, 2);
    }

    protected static function normalizeBudget(mixed $budget): ?float
    {
        if (! is_numeric($budget)) {
            return null;
        }

        $budget = (float) $budget;

        return $budget >= 0
            ? $budget
            : null;
    }

    protected static function kworkRate(float $budget): float
    {
        if ($budget <= 30000) {
            return 0.20;
        }

        if ($budget <= 300000) {
            return 0.12;
        }

        return 0.075;
    }

    protected static function flRate(float $budget): float
    {
        if ($budget <= 35000) {
            return 0.20;
        }

        if ($budget <= 350000) {
            return 0.12;
        }

        return 0.07;
    }
}

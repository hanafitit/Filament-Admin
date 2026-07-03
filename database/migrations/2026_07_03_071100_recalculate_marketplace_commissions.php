<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $orders = DB::table('orders')
            ->join('sources', 'orders.source_id', '=', 'sources.id')
            ->whereIn('sources.slug', ['kwork', 'fl'])
            ->select('orders.id', 'orders.budget', 'sources.slug')
            ->get();

        foreach ($orders as $order) {
            DB::table('orders')
                ->where('id', $order->id)
                ->update([
                    'commission' => $this->calculateCommission($order->slug, (float) $order->budget),
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        //
    }

    private function calculateCommission(string $sourceSlug, float $budget): float
    {
        $rate = match ($sourceSlug) {
            'kwork' => $this->kworkRate($budget),
            'fl' => $this->flRate($budget),
        };

        return round($budget * $rate, 2);
    }

    private function kworkRate(float $budget): float
    {
        if ($budget <= 30000) {
            return 0.20;
        }

        if ($budget <= 300000) {
            return 0.12;
        }

        return 0.075;
    }

    private function flRate(float $budget): float
    {
        if ($budget <= 35000) {
            return 0.20;
        }

        if ($budget <= 350000) {
            return 0.12;
        }

        return 0.07;
    }
};

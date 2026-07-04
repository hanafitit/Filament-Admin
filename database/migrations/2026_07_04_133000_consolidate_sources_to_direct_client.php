<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        DB::table('sources')->updateOrInsert(
            ['slug' => 'kwork'],
            ['name' => 'Kwork', 'updated_at' => $now, 'created_at' => $now],
        );

        DB::table('sources')->updateOrInsert(
            ['slug' => 'fl'],
            ['name' => 'FL', 'updated_at' => $now, 'created_at' => $now],
        );

        DB::table('sources')->updateOrInsert(
            ['slug' => 'direct'],
            ['name' => 'Прямой клиент', 'updated_at' => $now, 'created_at' => $now],
        );

        $directId = DB::table('sources')->where('slug', 'direct')->value('id');

        if ($directId !== null) {
            DB::table('orders')
                ->whereIn('source_id', function ($query) {
                    $query->select('id')
                        ->from('sources')
                        ->whereIn('slug', ['telegram', 'repeat']);
                })
                ->update([
                    'source_id' => $directId,
                    'updated_at' => $now,
                ]);
        }

        DB::table('sources')
            ->whereNotIn('slug', ['kwork', 'fl', 'direct'])
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $now = now();

        DB::table('sources')->updateOrInsert(
            ['slug' => 'telegram'],
            ['name' => 'Telegram', 'updated_at' => $now, 'created_at' => $now],
        );

        DB::table('sources')->updateOrInsert(
            ['slug' => 'repeat'],
            ['name' => 'Повторный клиент', 'updated_at' => $now, 'created_at' => $now],
        );
    }
};

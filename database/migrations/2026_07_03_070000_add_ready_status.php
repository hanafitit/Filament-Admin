<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('statuses')->updateOrInsert(
            ['name' => 'Готово'],
            [
                'color' => 'success',
                'sort_order' => 5,
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        DB::table('statuses')
            ->where('name', 'Оплачен')
            ->update([
                'sort_order' => 6,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('statuses')
            ->where('name', 'Готово')
            ->delete();

        DB::table('statuses')
            ->where('name', 'Оплачен')
            ->update([
                'sort_order' => 5,
                'updated_at' => now(),
            ]);
    }
};

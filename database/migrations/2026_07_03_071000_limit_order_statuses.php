<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $desiredStatuses = [
            'Новый' => ['color' => 'primary', 'sort_order' => 1],
            'В работе/на проверке' => ['color' => 'warning', 'sort_order' => 2],
            'Сдан' => ['color' => 'success', 'sort_order' => 3],
            'Оплачен' => ['color' => 'primary', 'sort_order' => 4],
        ];

        foreach ($desiredStatuses as $name => $attributes) {
            DB::table('statuses')->updateOrInsert(
                ['name' => $name],
                [
                    'color' => $attributes['color'],
                    'sort_order' => $attributes['sort_order'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        $targetIds = DB::table('statuses')
            ->whereIn('name', array_keys($desiredStatuses))
            ->pluck('id', 'name');

        foreach ([
            'Ожидает ТЗ' => 'Новый',
            'Оценка' => 'Новый',
            'В работе' => 'В работе/на проверке',
            'На проверке' => 'В работе/на проверке',
            'Правки' => 'В работе/на проверке',
            'Пауза' => 'В работе/на проверке',
            'Готово' => 'Сдан',
            'Ожидает оплаты' => 'Сдан',
            'Отменен' => 'Сдан',
        ] as $from => $to) {
            $fromId = DB::table('statuses')
                ->where('name', $from)
                ->value('id');

            if (! $fromId || ! isset($targetIds[$to])) {
                continue;
            }

            DB::table('orders')
                ->where('status_id', $fromId)
                ->update(['status_id' => $targetIds[$to]]);
        }

        DB::table('orders')
            ->whereIn(
                'status_id',
                DB::table('statuses')
                    ->whereNotIn('name', array_keys($desiredStatuses))
                    ->pluck('id'),
            )
            ->update(['status_id' => $targetIds['Новый']]);

        DB::table('statuses')
            ->whereNotIn('name', array_keys($desiredStatuses))
            ->delete();
    }

    public function down(): void
    {
        foreach ([
            ['name' => 'Ожидает ТЗ', 'color' => 'gray', 'sort_order' => 1],
            ['name' => 'Оценка', 'color' => 'info', 'sort_order' => 2],
            ['name' => 'Новый', 'color' => 'primary', 'sort_order' => 3],
            ['name' => 'В работе', 'color' => 'warning', 'sort_order' => 4],
            ['name' => 'На проверке', 'color' => 'info', 'sort_order' => 5],
            ['name' => 'Правки', 'color' => 'warning', 'sort_order' => 6],
            ['name' => 'Сдан', 'color' => 'success', 'sort_order' => 7],
            ['name' => 'Ожидает оплаты', 'color' => 'warning', 'sort_order' => 8],
            ['name' => 'Оплачен', 'color' => 'primary', 'sort_order' => 9],
            ['name' => 'Готово', 'color' => 'success', 'sort_order' => 10],
            ['name' => 'Пауза', 'color' => 'gray', 'sort_order' => 11],
            ['name' => 'Отменен', 'color' => 'danger', 'sort_order' => 12],
        ] as $status) {
            DB::table('statuses')->updateOrInsert(
                ['name' => $status['name']],
                [
                    'color' => $status['color'],
                    'sort_order' => $status['sort_order'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }
    }
};

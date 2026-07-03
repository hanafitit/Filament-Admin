<?php

namespace Database\Seeders;

use App\Models\Source;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            ['slug' => 'kwork', 'name' => 'Kwork'],
            ['slug' => 'fl', 'name' => 'FL.ru'],
            ['slug' => 'direct', 'name' => 'Прямой клиент'],
            ['slug' => 'telegram', 'name' => 'Telegram'],
            ['slug' => 'repeat', 'name' => 'Повторный клиент'],
        ] as $source) {
            Source::updateOrCreate(
                ['slug' => $source['slug']],
                ['name' => $source['name']],
            );
        }

        foreach ([
            ['name' => 'Новый', 'color' => 'primary', 'sort_order' => 1],
            ['name' => 'В работе/на проверке', 'color' => 'warning', 'sort_order' => 2],
            ['name' => 'Сдан', 'color' => 'success', 'sort_order' => 3],
            ['name' => 'Оплачен', 'color' => 'primary', 'sort_order' => 4],
        ] as $status) {
            Status::updateOrCreate(
                ['name' => $status['name']],
                [
                    'color' => $status['color'],
                    'sort_order' => $status['sort_order'],
                ],
            );
        }

        User::updateOrCreate(
            ['email' => 'admin@crm.local'],
            [
                'name' => 'Владелец',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'manager@crm.local'],
            [
                'name' => 'Менеджер',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'executor@crm.local'],
            [
                'name' => 'Исполнитель Один',
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'executor2@crm.local'],
            [
                'name' => 'Исполнитель Два',
                'password' => Hash::make('password'),
            ]
        );

        Role::findOrCreate('super_admin');
        Role::findOrCreate('manager');
        Role::findOrCreate('executor');

        User::where('email', 'admin@crm.local')
            ->first()?->syncRoles(['super_admin']);

        User::where('email', 'manager@crm.local')
            ->first()?->syncRoles(['manager']);

        User::where('email', 'executor@crm.local')
            ->first()?->syncRoles(['executor']);

        User::where('email', 'executor2@crm.local')
            ->first()?->syncRoles(['executor']);

        $this->call(OrderSeeder::class);
    }
}

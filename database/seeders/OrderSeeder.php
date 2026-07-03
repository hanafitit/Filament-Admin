<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Source;
use App\Models\Status;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminId = User::query()
            ->where('email', 'admin@crm.local')
            ->value('id');

        if (! $adminId) {
            return;
        }

        $managerId = User::query()
            ->where('email', 'manager@crm.local')
            ->value('id') ?: $adminId;
        $executorOneId = User::query()
            ->where('email', 'executor@crm.local')
            ->value('id') ?: $adminId;
        $executorTwoId = User::query()
            ->where('email', 'executor2@crm.local')
            ->value('id') ?: $adminId;

        $sources = Source::query()->pluck('id', 'slug');
        $statuses = Status::query()->pluck('id', 'name');

        foreach ([
            'Сайт для event-агентства' => 'Сайт для ивент-агентства',
            'API для мобильного приложения' => 'Серверная часть для мобильного приложения',
            'Дизайн презентации SaaS' => 'Дизайн презентации сервиса',
        ] as $oldTitle => $newTitle) {
            Order::query()
                ->where('title', $oldTitle)
                ->update(['title' => $newTitle]);
        }

        $orders = [
            [
                'title' => 'Лендинг для стоматологии',
                'description' => 'Сверстать адаптивный лендинг и подключить форму заявки.',
                'source_id' => $sources['kwork'] ?? null,
                'status_id' => $statuses['Новый'] ?? null,
                'budget' => 45000,
                'commission' => 4500,
                'deadline' => now()->addDays(3),
                'payment_deadline' => now()->addDays(5),
                'user_id' => $executorOneId,
            ],
            [
                'title' => 'Интернет-магазин косметики',
                'description' => 'Доработать каталог, корзину и оплату на Laravel.',
                'source_id' => $sources['fl'] ?? null,
                'status_id' => $statuses['В работе/на проверке'] ?? null,
                'budget' => 120000,
                'commission' => 12000,
                'deadline' => now()->addDays(10),
                'payment_deadline' => now()->addDays(14),
                'user_id' => $executorTwoId,
            ],
            [
                'title' => 'Telegram-бот для записи',
                'description' => 'Сделать бота с админкой и уведомлениями.',
                'source_id' => $sources['direct'] ?? null,
                'status_id' => $statuses['В работе/на проверке'] ?? null,
                'budget' => 80000,
                'commission' => 0,
                'deadline' => now()->addDays(2),
                'payment_deadline' => now()->addDays(7),
                'user_id' => $executorOneId,
            ],
            [
                'title' => 'CRM для отдела продаж',
                'description' => 'Подготовить модуль отчётов и экспорт в Excel.',
                'source_id' => $sources['direct'] ?? null,
                'status_id' => $statuses['Сдан'] ?? null,
                'budget' => 150000,
                'commission' => 0,
                'deadline' => now()->subDay(),
                'payment_deadline' => now()->addDays(3),
                'user_id' => $executorTwoId,
            ],
            [
                'title' => 'Редизайн сайта юриста',
                'description' => 'Обновить UI, мобильную версию и контентные блоки.',
                'source_id' => $sources['kwork'] ?? null,
                'status_id' => $statuses['Оплачен'] ?? null,
                'budget' => 60000,
                'commission' => 6000,
                'deadline' => now()->subDays(4),
                'payment_deadline' => now()->subDay(),
                'user_id' => $executorOneId,
            ],
            [
                'title' => 'Поддержка сайта онлайн-школы',
                'description' => 'Разобрать список задач от клиента и подготовить план работ.',
                'source_id' => $sources['repeat'] ?? null,
                'status_id' => $statuses['Новый'] ?? null,
                'budget' => 30000,
                'commission' => 0,
                'deadline' => now()->addDays(8),
                'payment_deadline' => now()->addDays(12),
                'user_id' => $executorTwoId,
            ],
            [
                'title' => 'Аудит Laravel-проекта',
                'description' => 'Проверить архитектуру, безопасность и подготовить оценку доработок.',
                'source_id' => $sources['telegram'] ?? null,
                'status_id' => $statuses['Новый'] ?? null,
                'budget' => 25000,
                'commission' => 0,
                'deadline' => now()->addDays(4),
                'payment_deadline' => now()->addDays(6),
                'user_id' => $executorOneId,
            ],
            [
                'title' => 'Личный кабинет для курсов',
                'description' => 'Сделать авторизацию, тарифы, уроки и прогресс студентов.',
                'source_id' => $sources['direct'] ?? null,
                'status_id' => $statuses['В работе/на проверке'] ?? null,
                'budget' => 180000,
                'commission' => 0,
                'deadline' => now()->addDays(18),
                'payment_deadline' => now()->addDays(25),
                'user_id' => $executorTwoId,
            ],
            [
                'title' => 'Интеграция amoCRM',
                'description' => 'Доработать вебхуки, синхронизацию статусов и повторную отправку ошибок.',
                'source_id' => $sources['fl'] ?? null,
                'status_id' => $statuses['В работе/на проверке'] ?? null,
                'budget' => 95000,
                'commission' => 9500,
                'deadline' => now()->addDays(6),
                'payment_deadline' => now()->addDays(9),
                'user_id' => $executorOneId,
            ],
            [
                'title' => 'Сайт для ивент-агентства',
                'description' => 'Проект сдан, ждем оплату по закрывающим документам.',
                'source_id' => $sources['repeat'] ?? null,
                'status_id' => $statuses['Сдан'] ?? null,
                'budget' => 70000,
                'commission' => 0,
                'deadline' => now()->subDays(2),
                'payment_deadline' => now()->addHours(18),
                'user_id' => $executorTwoId,
            ],
            [
                'title' => 'Парсер заявок Kwork',
                'description' => 'Клиент временно заморозил интеграцию до согласования лимитов интерфейса.',
                'source_id' => $sources['kwork'] ?? null,
                'status_id' => $statuses['В работе/на проверке'] ?? null,
                'budget' => 110000,
                'commission' => 11000,
                'deadline' => now()->addDays(20),
                'payment_deadline' => now()->addDays(27),
                'user_id' => $executorOneId,
            ],
            [
                'title' => 'Баннеры для маркетплейса',
                'description' => 'Заказ отменен клиентом до старта разработки.',
                'source_id' => $sources['telegram'] ?? null,
                'status_id' => $statuses['Сдан'] ?? null,
                'budget' => 20000,
                'commission' => 0,
                'deadline' => now()->addDays(5),
                'payment_deadline' => null,
                'user_id' => $executorTwoId,
            ],
            [
                'title' => 'Модуль Excel-экспорта',
                'description' => 'Готовый модуль экспорта финансового отчета и заказов.',
                'source_id' => $sources['direct'] ?? null,
                'status_id' => $statuses['Сдан'] ?? null,
                'budget' => 55000,
                'commission' => 0,
                'deadline' => now()->subDays(1),
                'payment_deadline' => now()->addDays(2),
                'user_id' => $executorOneId,
            ],
            [
                'title' => 'Техническая поддержка WordPress',
                'description' => 'Закрытый пакет поддержки и исправлений за месяц.',
                'source_id' => $sources['repeat'] ?? null,
                'status_id' => $statuses['Оплачен'] ?? null,
                'budget' => 35000,
                'commission' => 0,
                'deadline' => now()->subDays(10),
                'payment_deadline' => now()->subDays(5),
                'user_id' => $executorTwoId,
            ],
            [
                'title' => 'Настройка рекламы для студии',
                'description' => 'Работы приняты, оплата ожидается сегодня вечером.',
                'source_id' => $sources['telegram'] ?? null,
                'status_id' => $statuses['Сдан'] ?? null,
                'budget' => 40000,
                'commission' => 0,
                'deadline' => now()->subDay(),
                'payment_deadline' => now()->addHours(20),
                'user_id' => $executorOneId,
            ],
            [
                'title' => 'Серверная часть для мобильного приложения',
                'description' => 'Спроектировать интерфейс обмена данными, токены доступа и документацию.',
                'source_id' => $sources['fl'] ?? null,
                'status_id' => $statuses['В работе/на проверке'] ?? null,
                'budget' => 220000,
                'commission' => 22000,
                'deadline' => now()->addDays(30),
                'payment_deadline' => now()->addDays(35),
                'user_id' => $executorTwoId,
            ],
            [
                'title' => 'Дизайн презентации сервиса',
                'description' => 'Подготовить презентацию продукта для продажи агентствам.',
                'source_id' => $sources['direct'] ?? null,
                'status_id' => $statuses['В работе/на проверке'] ?? null,
                'budget' => 28000,
                'commission' => 0,
                'deadline' => now()->addDay(),
                'payment_deadline' => now()->addDays(4),
                'user_id' => $executorOneId,
            ],
            [
                'title' => 'Миграция CRM на PostgreSQL',
                'description' => 'Перенести данные, проверить индексы и подготовить резервную копию.',
                'source_id' => $sources['repeat'] ?? null,
                'status_id' => $statuses['Сдан'] ?? null,
                'budget' => 130000,
                'commission' => 0,
                'deadline' => now()->subDays(3),
                'payment_deadline' => now()->addDays(2),
                'user_id' => $executorTwoId,
            ],
        ];

        foreach ($orders as $payload) {
            if (! $payload['source_id'] || ! $payload['status_id']) {
                continue;
            }

            Order::updateOrCreate(
                ['title' => $payload['title']],
                array_merge($payload, [
                    'user_id' => $payload['user_id'] ?? $adminId,
                    'manager_id' => $managerId,
                ])
            );
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Source;
use App\Models\Status;
use App\Models\User;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\PaymentDeadlineReminderNotification;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OrderTelegramNotificationsTest extends TestCase
{
    public function test_created_order_sends_telegram_notification_when_configured(): void
    {
        Notification::fake();
        $this->configureTelegram();
        [$source, $status] = $this->createCatalog();

        $this->makeOrder($source, $status, User::factory()->create());

        Notification::assertSentOnDemand(OrderCreatedNotification::class, function ($notification, array $channels, object $notifiable): bool {
            return ($notifiable->routes['telegram'] ?? null) === '100500';
        });
    }

    public function test_status_change_sends_telegram_notification_when_configured(): void
    {
        Notification::fake();
        $this->configureTelegram();
        [$source, $fromStatus] = $this->createCatalog();
        $toStatus = Status::query()->create([
            'name' => 'В работе',
            'color' => 'warning',
            'sort_order' => 2,
        ]);
        $order = $this->makeOrder($source, $fromStatus, User::factory()->create());

        Notification::fake();

        $order->update(['status_id' => $toStatus->id]);

        Notification::assertSentOnDemand(OrderStatusChangedNotification::class, function ($notification, array $channels, object $notifiable): bool {
            return ($notifiable->routes['telegram'] ?? null) === '100500';
        });
    }

    public function test_payment_deadline_command_sends_telegram_reminders_when_configured(): void
    {
        Notification::fake();
        $this->configureTelegram();
        [$source, $status] = $this->createCatalog();
        $this->makeOrder($source, $status, User::factory()->create(), [
            'payment_deadline' => now()->addHours(2),
        ]);

        Notification::fake();

        $this->artisan('orders:remind-payments')
            ->assertExitCode(0);

        Notification::assertSentOnDemand(PaymentDeadlineReminderNotification::class, function ($notification, array $channels, object $notifiable): bool {
            return ($notifiable->routes['telegram'] ?? null) === '100500';
        });
    }

    protected function configureTelegram(): void
    {
        config([
            'services.telegram.bot_token' => 'test-token',
            'services.telegram.chat_id' => '100500',
        ]);
    }

    protected function createCatalog(): array
    {
        $source = Source::query()->create([
            'name' => 'Website',
            'slug' => 'website',
        ]);

        $status = Status::query()->create([
            'name' => 'Новый',
            'color' => 'gray',
            'sort_order' => 1,
        ]);

        return [$source, $status];
    }

    protected function makeOrder(Source $source, Status $status, User $user, array $overrides = []): Order
    {
        return Order::query()->create(array_merge([
            'title' => 'Test order',
            'description' => 'Description',
            'source_id' => $source->id,
            'status_id' => $status->id,
            'user_id' => $user->id,
            'budget' => 1000,
            'commission' => 100,
            'deadline' => now()->addDay(),
            'payment_deadline' => now()->addDays(2),
        ], $overrides));
    }
}

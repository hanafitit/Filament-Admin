<?php

namespace App\Notifications;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Order $order,
    ) {}

    public function via(object $notifiable): array
    {
        return [TelegramChannel::class];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $order = $this->order->loadMissing('status');

        if ($order->exists) {
            $order = $order->fresh(['status']) ?? $order;
        }

        $url = OrderResource::getUrl(
            'edit',
            ['record' => $order->getKey()],
            isAbsolute: true,
            panel: 'admin',
        );

        $title = TelegramMessage::escapeMarkdown($order->title) ?? $order->title;
        $status = TelegramMessage::escapeMarkdown($order->status?->name ?? 'Без статуса') ?? ($order->status?->name ?? 'Без статуса');
        $budget = number_format((float) $order->budget, 2, '.', ' ');
        $deadline = $order->deadline?->format('d.m.Y H:i') ?? 'Не указан';

        $message = TelegramMessage::create()
            ->token(config('services.telegram.bot_token'))
            ->content(
                "Смена статуса заказа!\n\n".
                "Заказ: {$title}\n".
                "Новый статус: {$status}\n".
                "Бюджет: {$budget} ₽\n".
                "Дедлайн: {$deadline}"
            );

        if ($this->canUseTelegramButtonUrl($url)) {
            $message->button('Открыть в системе', $url);
        }

        return $message;
    }

    protected function canUseTelegramButtonUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return false;
        }

        return ! in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }
}

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

class PaymentDeadlineReminderNotification extends Notification implements ShouldQueue
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
        $order = $this->order->loadMissing(['source', 'user']);

        if ($order->exists) {
            $order = $order->fresh(['source', 'user']) ?? $order;
        }

        $url = OrderResource::getUrl(
            'edit',
            ['record' => $order->getKey()],
            isAbsolute: true,
            panel: 'admin',
        );

        $title = TelegramMessage::escapeMarkdown($order->title) ?? $order->title;
        $source = TelegramMessage::escapeMarkdown($order->source?->name ?? 'Без источника') ?? ($order->source?->name ?? 'Без источника');
        $executor = TelegramMessage::escapeMarkdown($order->user?->name ?? 'Не назначен') ?? ($order->user?->name ?? 'Не назначен');
        $budget = number_format((float) $order->budget, 2, '.', ' ');
        $paymentDeadline = $order->payment_deadline?->format('d.m.Y H:i') ?? 'Не указан';

        return TelegramMessage::create()
            ->token(config('services.telegram.bot_token'))
            ->content(
                "⏰ *Ожидается оплата по заказу*\n\n".
                "📦 *Заказ:* {$title}\n".
                "🔗 *Источник:* {$source}\n".
                "👤 *Исполнитель:* {$executor}\n".
                "💰 *К поступлению:* {$budget} ₽\n".
                "📅 *Дедлайн оплаты:* {$paymentDeadline}"
            )
            ->button('Открыть в системе', $url);
    }
}

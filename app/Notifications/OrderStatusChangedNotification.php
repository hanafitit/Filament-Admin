<?php

namespace App\Notifications;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Number;
use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;

class OrderStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Order $order,
        protected ?string $previousStatusName = null,
        protected ?string $changedByName = null,
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
        $statusTransition = $this->formatStatusTransition(
            $this->previousStatusName,
            $order->status?->name ?? 'Без статуса',
        );
        $escapedStatusTransition = TelegramMessage::escapeMarkdown($statusTransition) ?? $statusTransition;
        $changedByName = $this->changedByName ?: 'Система';
        $changedBy = TelegramMessage::escapeMarkdown($changedByName) ?? $changedByName;
        $budgetText = $this->formatBudget($order->budget);
        $budget = TelegramMessage::escapeMarkdown($budgetText) ?? $budgetText;
        $deadline = $order->deadline?->format('d.m.Y H:i') ?? 'Не указан';

        $message = TelegramMessage::create()
            ->token(config('services.telegram.bot_token'))
            ->content(
                "🔔 Смена статуса заказа!\n\n".
                "📁 Заказ: {$title}\n".
                "🔄 Статус: {$escapedStatusTransition}\n".
                "👤 Кто изменил: {$changedBy}\n".
                "💰 Бюджет: {$budget}\n".
                "📅 Дедлайн: {$deadline}"
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

    protected function formatStatusTransition(?string $previousStatusName, string $currentStatusName): string
    {
        $current = $this->decorateStatus($currentStatusName);

        if (blank($previousStatusName) || $previousStatusName === $currentStatusName) {
            return $current;
        }

        return $this->decorateStatus($previousStatusName).' ➡️ '.$current;
    }

    protected function decorateStatus(string $statusName): string
    {
        return match (mb_strtolower(trim($statusName))) {
            'новый' => 'Новый 🆕',
            'в работе', 'в работе/на проверке' => 'В работе ⚙️',
            'сдан' => 'Сдан ✅',
            'оплачен' => 'Оплачен 💰',
            default => $statusName,
        };
    }

    protected function formatBudget(mixed $budget): string
    {
        $amount = (float) $budget;

        if (fmod($amount, 1.0) === 0.0) {
            return Number::format($amount, 0, locale: 'ru').' ₽';
        }

        return Number::format($amount, 2, locale: 'ru').' ₽';
    }
}

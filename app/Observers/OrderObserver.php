<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\Status;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class OrderObserver
{
    public function created(Order $order): void
    {
        $this->sendTelegramNotification(new OrderCreatedNotification($order->fresh(['source', 'status', 'user']) ?? $order));
    }

    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status_id')) {
            return;
        }

        $previousStatusName = Status::query()
            ->whereKey($order->getOriginal('status_id'))
            ->value('name');

        $this->sendTelegramNotification(new OrderStatusChangedNotification(
            $order->fresh(['status']) ?? $order,
            $previousStatusName,
            auth()->user()?->name,
        ));
    }

    protected function sendTelegramNotification(Notification $notification): void
    {
        if (blank(config('services.telegram.chat_id')) || blank(config('services.telegram.bot_token'))) {
            return;
        }

        NotificationFacade::route('telegram', config('services.telegram.chat_id'))
            ->notify($notification);
    }
}

<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Status;
use App\Notifications\PaymentDeadlineReminderNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

#[Signature('orders:remind-payments')]
#[Description('Поиск заказов с приближающимся дедлайном оплаты')]
class RemandPaymentDeadlines extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $paidStatusId = Status::query()
            ->where('name', 'Оплачен')
            ->value('id');

        $query = Order::query()
            ->whereNotNull('payment_deadline')
            ->whereBetween('payment_deadline', [now(), now()->addHours(24)]);

        if ($paidStatusId) {
            $query->where('status_id', '!=', $paidStatusId);
        }

        $incomingOrders = $query->get();
        $telegramIsConfigured = filled(config('services.telegram.chat_id'))
            && filled(config('services.telegram.bot_token'));

        foreach ($incomingOrders as $order) {
            if ($telegramIsConfigured) {
                Notification::route('telegram', config('services.telegram.chat_id'))
                    ->notify(new PaymentDeadlineReminderNotification($order));
            }

            Log::info(
                "Внимание! По заказу '{$order->title}' ожидается оплата до {$order->payment_deadline->format('d.m.Y H:i')}",
                [
                    'order_id' => $order->id,
                    'payment_deadline' => $order->payment_deadline?->toDateTimeString(),
                    'telegram_sent' => $telegramIsConfigured,
                ],
            );
        }

        $this->info("Найдено заказов с приближающейся оплатой: {$incomingOrders->count()}");

        return self::SUCCESS;
    }
}

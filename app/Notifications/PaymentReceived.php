<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class PaymentReceived extends Notification
{
    use Queueable;

    public function __construct(
        protected Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $order = $this->order;

        $text = "✅ *Payment Received!*\n\n"
            . "*Order:* #{$order->order_number}\n"
            . "*Customer:* {$order->user->name}\n"
            . "*Amount:* \${$order->total}\n"
            . "*Method:* {$order->payment_method}\n"
            . "*Status:* {$order->payment_status}\n\n"
            . "📦 *Order Status:* {$order->status}";

        return TelegramMessage::create()
            ->to(method_exists($notifiable, 'routeNotificationForTelegram') ? $notifiable->routeNotificationForTelegram() : (string) config('services.telegram.chat_id'))
            ->content($text)
            ->button('View Order', route('admin.orders.show', $order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'amount' => $this->order->total,
            'payment_status' => $this->order->payment_status,
        ];
    }
}

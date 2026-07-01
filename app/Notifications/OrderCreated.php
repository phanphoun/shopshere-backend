<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class OrderCreated extends Notification
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
        $items = $order->items->take(5)->map(fn ($item) => 
            "• {$item->product_name} × {$item->quantity} — \${$item->subtotal}"
        )->implode("\n");

        $text = "🛒 *New Order Received!*\n\n"
            . "*Order:* #{$order->order_number}\n"
            . "*Customer:* {$order->user->name}\n"
            . "*Email:* {$order->user->email}\n"
            . "*Phone:* {$order->phone}\n"
            . "*Total:* \${$order->total}\n"
            . "*Payment:* {$order->payment_method}\n"
            . "*Status:* {$order->status}\n\n"
            . "*Items:*\n{$items}";

        if ($order->items->count() > 5) {
            $text .= "\n… and " . ($order->items->count() - 5) . " more items";
        }

        return TelegramMessage::create()
            ->to($notifiable->routeNotificationForTelegram())
            ->content($text)
            ->button('View Order', route('admin.orders.show', $order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total' => $this->order->total,
        ];
    }
}

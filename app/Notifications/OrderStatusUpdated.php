<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class OrderStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        protected Order $order,
        protected string $oldStatus,
        protected string $newStatus
    ) {}

    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram(object $notifiable): TelegramMessage
    {
        $order = $this->order;

        $text = "📋 *Order Status Updated*\n\n"
            . "*Order:* #{$order->order_number}\n"
            . "*Customer:* {$order->user->name}\n"
            . "*Status:* {$this->oldStatus} → {$this->newStatus}\n"
            . "*Payment:* {$order->payment_status}\n"
            . "*Total:* \${$order->total}\n";

        if ($order->isDelivered()) {
            $text .= "\n✅ *Order has been delivered!*";
        } elseif ($order->isShipped()) {
            $text .= "\n📦 *Order has been shipped!*";
        } elseif ($order->isCancelled()) {
            $text .= "\n❌ *Order has been cancelled.*";
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
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }
}

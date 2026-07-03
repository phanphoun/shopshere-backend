<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderCreated;
use App\Notifications\OrderStatusUpdated;
use App\Notifications\PaymentReceived;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use NotificationChannels\Telegram\Exceptions\CouldNotSendNotification;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public function updateStatus(Order $order, string $status): Order
    {
        $allowedStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_PROCESSING,
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
            Order::STATUS_CANCELLED,
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            throw ValidationException::withMessages([
                'status' => 'Invalid order status.',
            ]);
        }

        $oldStatus = $order->status;
        $order = $this->orderRepository->updateStatus($order, $status);

        // Send Telegram notifications about the status change
        $this->sendStatusUpdateNotifications($order, $oldStatus, $status);

        return $order;
    }

    /**
     * Mark an order as paid and send payment notification.
     */
    public function markAsPaid(Order $order): Order
    {
        $order->payment_status = Order::PAYMENT_PAID;
        $order->save();

        $this->sendPaymentNotifications($order);

        return $order;
    }

    /**
     * Send Telegram notifications about order status changes to admin users.
     */
    protected function sendStatusUpdateNotifications(Order $order, string $oldStatus, string $newStatus): void
    {
        $order->load('user');

        $admins = User::where('role', User::ROLE_ADMIN)
            ->whereNotNull('telegram_chat_id')
            ->get();

        if ($admins->isNotEmpty()) {
            try {
                Notification::send($admins, new OrderStatusUpdated($order, $oldStatus, $newStatus));
            } catch (CouldNotSendNotification $e) {
                Log::warning('Order status notification failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }

            return;
        }

        $adminChatId = config('services.telegram.chat_id');

        if ($adminChatId) {
            try {
                Notification::route('telegram', $adminChatId)
                    ->notify(new OrderStatusUpdated($order, $oldStatus, $newStatus));
            } catch (CouldNotSendNotification $e) {
                Log::warning('Order status notification failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Send Telegram notifications about payment received.
     */
    protected function sendPaymentNotifications(Order $order): void
    {
        $order->load('user');

        $admins = User::where('role', User::ROLE_ADMIN)
            ->whereNotNull('telegram_chat_id')
            ->get();

        if ($admins->isNotEmpty()) {
            try {
                Notification::send($admins, new PaymentReceived($order));
            } catch (CouldNotSendNotification $e) {
                Log::warning('Payment notification failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }

            return;
        }

        $adminChatId = config('services.telegram.chat_id');

        if ($adminChatId) {
            try {
                Notification::route('telegram', $adminChatId)
                    ->notify(new PaymentReceived($order));
            } catch (CouldNotSendNotification $e) {
                Log::warning('Payment notification failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }
    }

}

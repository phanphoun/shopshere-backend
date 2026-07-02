<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    protected string $apiUrl;

    public function __construct()
    {
        $token = config('services.telegram.token');
        $this->apiUrl = "https://api.telegram.org/bot{$token}";
    }

    /**
     * Set the Telegram webhook URL.
     */
    public function setWebhook(string $url): array
    {
        $response = Http::post("{$this->apiUrl}/setWebhook", [
            'url' => $url,
            'allowed_updates' => ['message', 'callback_query'],
        ]);

        $result = $response->json();

        if (!$result['ok'] ?? false) {
            Log::error('Telegram webhook setup failed', $result);
        }

        return $result;
    }

    /**
     * Remove the Telegram webhook.
     */
    public function removeWebhook(): array
    {
        $response = Http::post("{$this->apiUrl}/deleteWebhook");

        return $response->json();
    }

    /**
     * Get current webhook info.
     */
    public function getWebhookInfo(): array
    {
        $response = Http::post("{$this->apiUrl}/getWebhookInfo");

        return $response->json();
    }

    /**
     * Send a text message.
     */
    public function sendMessage(string $chatId, string $text, array $replyMarkup = []): array
    {
        $response = Http::post("{$this->apiUrl}/sendMessage", array_filter([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_markup' => $replyMarkup,
        ]));

        return $response->json();
    }

    /**
     * Send a photo message with caption.
     */
    public function sendPhoto(string $chatId, string $photoUrl, string $caption, array $replyMarkup = []): array
    {
        $response = Http::post("{$this->apiUrl}/sendPhoto", array_filter([
            'chat_id' => $chatId,
            'photo' => $photoUrl,
            'caption' => $caption,
            'parse_mode' => 'Markdown',
            'reply_markup' => $replyMarkup,
        ]));

        return $response->json();
    }

    /**
     * Send a raw Telegram API request.
     */
    public function sendRequest(string $method, array $payload): array
    {
        $response = Http::post("{$this->apiUrl}/{$method}", $payload);

        return $response->json();
    }

    /**
     * Verify that a chat_id belongs to an admin user by matching the stored chat_id.
     */
    public function verifyChatId(User $user, string $chatId): bool
    {
        return $user->telegram_chat_id === $chatId;
    }

    /**
     * Reply markup keyboard: main menu buttons.
     *
     * @return array<mixed>
     */
    public static function mainMenuKeyboard(): array
    {
        return [
            'keyboard' => [
                ['/status'],
                ['/report My order is delayed', '/help'],
                ['/faq', '/orders'],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];
    }

    /**
     * Reply markup keyboard: orders/filter buttons.
     *
     * @return array<mixed>
     */
    public static function ordersKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => 'Recent orders', 'callback_data' => 'orders_recent'],
                    ['text' => 'Pending', 'callback_data' => 'orders_pending'],
                ],
                [
                    ['text' => 'Track order', 'callback_data' => 'orders_status'],
                    ['text' => 'Search products', 'callback_data' => 'search_prompt'],
                ],
            ],
        ];
    }

    /**
     * Reply markup keyboard: support quick actions.
     *
     * @return array<mixed>
     */
    public static function supportActionsKeyboard(string $conversationId): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => 'Track order', 'callback_data' => "orders_recent_{$conversationId}"],
                    ['text' => 'Search products', 'callback_data' => 'search_prompt'],
                ],
                [
                    ['text' => 'Contact support', 'callback_data' => 'faq_support'],
                    ['text' => 'Main menu', 'callback_data' => 'help'],
                ],
            ],
        ];
    }

    /**
     * Reply markup keyboard: FAQ options.
     *
     * @return array<mixed>
     */
    public static function faqKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => 'Track order', 'callback_data' => 'orders_recent'],
                    ['text' => 'Search products', 'callback_data' => 'search_prompt'],
                ],
                [
                    ['text' => 'Contact support', 'callback_data' => 'faq_support'],
                    ['text' => 'Main menu', 'callback_data' => 'help'],
                ],
            ],
        ];
    }

    /**
     * Reply markup keyboard: search results / product options.
     *
     * @return array<mixed>
     */
    public static function productSearchKeyboard(string $query = ''): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => 'All products', 'callback_data' => 'products_all'],
                    ['text' => 'Recent orders', 'callback_data' => 'orders_recent'],
                ],
                [
                    ['text' => 'Contact support', 'callback_data' => 'faq_support'],
                    ['text' => 'Main menu', 'callback_data' => 'help'],
                ],
            ],
        ];
    }

    /**
     * Reply markup keyboard: account link/login options.
     *
     * @return array<mixed>
     */
    public static function accountLinkKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => 'Open website to link', 'url' => 'https://t.me/' . (config('services.telegram.bot_username') ?: 'ShopSphereBot')],
                ],
                [
                    ['text' => 'Login with email/password', 'callback_data' => 'login_prompt'],
                ],
                [
                    ['text' => 'Help', 'callback_data' => 'help'],
                ],
            ],
        ];
    }

    /**
     * Reply markup keyboard: order tracking quick statuses.
     *
     * @return array<mixed>
     */
    public static function orderTrackingKeyboard(): array
    {
        return [
            'inline_keyboard' => [
                [
                    ['text' => 'Pending', 'callback_data' => 'orders_pending'],
                    ['text' => 'Status', 'callback_data' => 'orders_status'],
                ],
                [
                    ['text' => 'Help', 'callback_data' => 'help'],
                    ['text' => 'Main menu', 'callback_data' => 'help'],
                ],
            ],
        ];
    }

    public static function productCardKeyboard(string $productId, bool $inStock): array
    {
        $actions = [];

        if ($inStock) {
            $actions[] = [
                ['text' => 'Order', 'callback_data' => "product_order_{$productId}"],
                ['text' => 'Ask for pay', 'callback_data' => "product_pay_{$productId}"],
            ];
        }

        $actions[] = [
            ['text' => 'Main menu', 'callback_data' => 'help'],
        ];

        return [
            'inline_keyboard' => $actions,
        ];
    }
}

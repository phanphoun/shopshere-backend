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
            'allowed_updates' => ['message'],
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
     * Send a direct message to a chat ID (for testing).
     */
    public function sendMessage(string $chatId, string $text): array
    {
        $response = Http::post("{$this->apiUrl}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ]);

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
     * Get the bot's info (username, etc.)
     */
    public function getBotInfo(): array
    {
        $response = Http::post("{$this->apiUrl}/getMe");

        return $response->json();
    }
}

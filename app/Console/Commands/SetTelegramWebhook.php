<?php

namespace App\Console\Commands;

use App\Services\TelegramBotService;
use Illuminate\Console\Command;

class SetTelegramWebhook extends Command
{
    protected $signature = 'telegram:set-webhook {url? : The webhook URL to set}';
    protected $description = 'Set or get the Telegram bot webhook URL';

    public function handle(TelegramBotService $telegramBotService): int
    {
        $url = $this->argument('url');

        if ($url) {
            $result = $telegramBotService->setWebhook($url);

            if ($result['ok'] ?? false) {
                $this->info("Webhook set successfully to: {$url}");

                return self::SUCCESS;
            }

            $this->error("Failed to set webhook: " . ($result['description'] ?? 'Unknown error'));

            return self::FAILURE;
        }

        // Show current webhook info
        $info = $telegramBotService->getWebhookInfo();

        if ($info['ok'] ?? false) {
            $this->line("Current webhook URL: " . ($info['result']['url'] ?? 'Not set'));
            $this->line("Pending updates: " . ($info['result']['pending_update_count'] ?? 0));
            $this->line("Last error date: " . ($info['result']['last_error_date'] ?? 'N/A'));
            $this->line("Last error message: " . ($info['result']['last_error_message'] ?? 'N/A'));
        } else {
            $this->error("Failed to get webhook info: " . ($info['description'] ?? 'Unknown error'));
        }

        return self::SUCCESS;
    }
}

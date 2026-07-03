<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$token = config('services.telegram.token');

if (empty($token)) {
    echo "Missing TELEGRAM_BOT_TOKEN in .env\n";
    exit(1);
}

$webhookUrl = 'https://dock-wake-false.ngrok-free.dev/api/telegram/webhook';

$webhookSecret = (string) config('services.telegram.webhook_secret');

$ch = curl_init("https://api.telegram.org/bot{$token}/deleteWebhook");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['drop_pending_updates' => 'true']);
$response = curl_exec($ch);
curl_close($ch);
echo "deleteWebhook: {$response}\n";

$ch = curl_init("https://api.telegram.org/bot{$token}/setWebhook");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
$fields = ['url' => $webhookUrl];
if ($webhookSecret !== '') {
    $fields['secret_token'] = $webhookSecret;
}
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
$response = curl_exec($ch);
curl_close($ch);
echo "setWebhook: {$response}\n";

$ch = curl_init("https://api.telegram.org/bot{$token}/getWebhookInfo");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
echo "getWebhookInfo: {$response}\n";

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use App\Services\TelegramBotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TelegramController extends Controller
{
    public function __construct(
        protected TelegramBotService $telegramBotService
    ) {
        $this->registerBotCommands();
    }

    private function registerBotCommands(): void
    {
        $commandPayload = [
            'commands' => [
                ['command' => 'start', 'description' => 'Start the bot'],
                ['command' => 'help', 'description' => 'Show help menu'],
                ['command' => 'menu', 'description' => 'Show main menu'],
                ['command' => 'status', 'description' => 'Connection status'],
                ['command' => 'id', 'description' => 'Show Telegram chat ID'],
                ['command' => 'report', 'description' => 'Chat with admin / send message'],
                ['command' => 'support', 'description' => 'Chat with admin / send message'],
                ['command' => 'orders', 'description' => 'Recent orders'],
                ['command' => 'track', 'description' => 'Track order by ID'],
                ['command' => 'search', 'description' => 'Search products'],
                ['command' => 'login', 'description' => 'Link account with email and password'],
                ['command' => 'faq', 'description' => 'Frequently asked questions'],
            ],
        ];

        try {
            $this->telegramBotService->sendRequest('setMyCommands', $commandPayload);
        } catch (\Throwable $e) {
            Log::warning('Failed to register telegram bot commands', ['error' => $e->getMessage()]);
        }
    }

    public function webhook(Request $request): JsonResponse
    {
        $secretToken = $request->header('X-Telegram-Bot-Api-Secret-Token');
        $expectedToken = config('services.telegram.webhook_secret');

        if ($expectedToken && !hash_equals($expectedToken, $secretToken)) {
            Log::warning('Telegram webhook received with invalid secret token');

            return response()->json(['ok' => false], 403);
        }

        $payload = $request->all();
        Log::debug('Telegram webhook received', $payload);

        $message = $payload['message'] ?? null;
        $callbackQuery = $payload['callback_query'] ?? null;

        if ($callbackQuery && isset($callbackQuery['data'], $callbackQuery['message']['chat']['id'])) {
            return $this->handleCallbackQuery($callbackQuery);
        }

        if (!$message || !isset($message['chat']['id'])) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) $message['chat']['id'];
        $text = trim((string) ($message['text'] ?? ''));
        $username = (string) ($message['from']['username'] ?? '');
        $firstName = (string) ($message['from']['first_name'] ?? ($username ?: 'Telegram user'));
        $userId = array_key_exists('id', $message['from'] ?? []) ? $message['from']['id'] : null;
        $mode = $this->normalizeBotMode(config('services.telegram.bot_mode'));

        if (Str::startsWith($text, '/start')) {
            $this->handleAccountLinking($chatId, $firstName, $username);

            return response()->json(['ok' => true]);
        }

        if (Str::startsWith($text, '/help') || Str::startsWith($text, '/menu')) {
            $this->telegramBotService->sendMessage($chatId, "Hi {$firstName} 👋\n\n" . $this->modeHelp($mode));

            return response()->json(['ok' => true]);
        }

        if ($text === '/id') {
            $this->telegramBotService->sendMessage($chatId, "Your Telegram chat ID: {$chatId}");

            return response()->json(['ok' => true]);
        }

        if (Str::startsWith($text, '/status')) {
            return $this->handleStatus($chatId);
        }

        if (Str::startsWith($text, '/search')) {
            return $this->handleSearch($chatId, $text);
        }

        if (Str::startsWith($text, '/product ')) {
            return $this->handleProductCard($chatId, $text);
        }

        if (Str::startsWith($text, '/track ')) {
            return $this->handleTrack($chatId, $text);
        }

        if (Str::startsWith($text, '/login ')) {
            return $this->handleTelegramLogin($chatId, $text);
        }

        if (Str::startsWith($text, '/faq')) {
            return $this->handleFaq($chatId, $mode, $text);
        }

        if (in_array($mode, ['support', 'both'], true) && (Str::startsWith($text, '/support') || Str::startsWith($text, '/report'))) {
            $parts = explode(' ', $text, 2);
            $messageBody = $parts[1] ?? '';

            if ($messageBody === '') {
                $this->telegramBotService->sendMessage(
                    $chatId,
                    "Use /report with a message.\nExample:\n/report My order is delayed."
                );

                return response()->json(['ok' => true]);
            }

            $conversation = $this->resolveSupportConversation($chatId, $firstName, $username, $userId);
            SupportMessage::create([
                'support_conversation_id' => $conversation->id,
                'sender_type' => 'telegram',
                'sender_name' => $firstName,
                'message' => $messageBody,
            ]);

            $this->notifyTelegramAdmin($conversation, $messageBody);
            $this->telegramBotService->sendMessage(
                $chatId,
                "✅ Your message was sent to support.\nConversation #{$conversation->id}",
                TelegramBotService::supportActionsKeyboard((string) $conversation->id)
            );

            return response()->json(['ok' => true]);
        }

        if (in_array($mode, ['notification', 'both'], true) && Str::startsWith($text, '/orders')) {
            return $this->handleOrders($chatId, $userId);
        }

        $this->telegramBotService->sendMessage(
            $chatId,
            "Hi {$firstName}, I didn’t get that one.\n\n"
                . "You can use any of these:\n"
                . "/help - all commands\n"
                . "/faq - frequently asked questions\n"
                . "/search <query> - find products\n"
                . "/track <order_id> - track order\n"
                . "/status - check connection\n"
                . "/report <message> - chat with admin",
            TelegramBotService::mainMenuKeyboard()
        );

        return response()->json(['ok' => true]);
    }

    protected function handleFaq(string $chatId, string $mode, string $text): JsonResponse
    {
        $body = trim((string) preg_replace('~^(/faq)(\s.*)?$~i', '$2', $text));
        $responses = [
            'product' => "Buying products:\n• Browse: use /search <query>\n• Example: /search t-shirt",
            'order' => "Order updates:\n• /orders — recent orders\n• /track <order_id> — track one order",
            'password' => "Account security:\n• Use /status to check connection\n• Contact support: /report <message>",
            'support' => "Support:\n• /report <message> — chat with admin\n• /help — view all commands",
            'login' => "Login / link Telegram:\n• /login email password (use your website account)",
        ];

        if ($body === '') {
            $lines = [
                'FAQ — choose a topic or type your question, like:',
                '',
                '1. /faq product',
                '2. /faq order',
                '3. /faq password',
                '4. /faq support',
                '5. /faq login',
                '',
                'You can also use:',
                '• /search <query>',
                '• /track <order_id>',
                '• /login email password',
                '• /report <message>',
            ];

            $this->telegramBotService->sendMessage(
                $chatId,
                implode("\n", $lines),
                TelegramBotService::faqKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        $lower = mb_strtolower($body);

        foreach ($responses as $key => $reply) {
            if (str_contains($lower, $key)) {
                $this->telegramBotService->sendMessage(
                    $chatId,
                    $reply,
                    TelegramBotService::faqKeyboard()
                );

                return response()->json(['ok' => true]);
            }
        }

        $this->telegramBotService->sendMessage(
            $chatId,
            "I can help with these topics:\n\n"
                . "Product / order / account help -> /search, /track, /login\n"
                . "Support -> /report <message>\n"
                . "All commands -> /help",
            TelegramBotService::faqKeyboard()
        );

        return response()->json(['ok' => true]);
    }

    protected function handleSearch(string $chatId, string $text): JsonResponse
    {
        $parts = explode(' ', $text, 2);
        $query = trim((string) ($parts[1] ?? ''));

        if ($query === '') {
            $this->telegramBotService->sendMessage(
                $chatId,
                "Use /search with a query.\nExample:\n/search jacket",
                TelegramBotService::productSearchKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        $products = Product::query()
            ->where(function ($queryBuilder) use ($query) {
                $queryBuilder->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            })
            ->where('status', true)
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'sku', 'price', 'discount_price']);

        if ($products->isEmpty()) {
            $this->telegramBotService->sendMessage(
                $chatId,
                "No products found for '{$query}'.",
                TelegramBotService::productSearchKeyboard($query)
            );

            return response()->json(['ok' => true]);
        }

        $lines = ["🔎 Search results for '{$query}'"];
        foreach ($products as $product) {
            $finalPrice = number_format((float) ($product->discount_price ?? $product->price), 2);
            $lines[] = "{$product->name}\n"
                . "SKU: {$product->sku}\n"
                . "Price: \${$finalPrice}";
        }

        $text = implode("\n\n", $lines);
        if (strlen($text) > 4096) {
            $text = substr($text, 0, 4093) . '...';
        }

        $keyboardButtons = [];
        foreach ($products as $product) {
            $keyboardButtons[] = [
                ['text' => "View {$product->name}", 'callback_data' => "product_{$product->id}"],
            ];
        }
        $keyboardButtons[] = [
            ['text' => 'Contact support', 'callback_data' => 'faq_support'],
            ['text' => 'Main menu', 'callback_data' => 'help'],
        ];

        $this->telegramBotService->sendMessage(
            $chatId,
            $text,
            ['inline_keyboard' => $keyboardButtons]
        );

        return response()->json(['ok' => true]);
    }

    protected function handleProductCard(string $chatId, string $text): JsonResponse
    {
        $parts = explode(' ', $text, 2);
        $productId = trim((string) ($parts[1] ?? ''));

        if ($productId === '' || !ctype_digit($productId)) {
            $this->telegramBotService->sendMessage(
                $chatId,
                "Use /product with an ID.\nExample:\n/product 1",
                ['inline_keyboard' => [
                    [
                        ['text' => 'Search products', 'callback_data' => 'search_prompt'],
                        ['text' => 'Main menu', 'callback_data' => 'help'],
                    ],
                ]]
            );

            return response()->json(['ok' => true]);
        }

        $product = Product::query()->find((int) $productId);

        if (!$product || !$product->status) {
            $this->telegramBotService->sendMessage(
                $chatId,
                "Product #{$productId} not found or unavailable.",
                ['inline_keyboard' => [
                    [
                        ['text' => 'Search products', 'callback_data' => 'search_prompt'],
                        ['text' => 'Main menu', 'callback_data' => 'help'],
                    ],
                ]]
            );

            return response()->json(['ok' => true]);
        }

        return $this->replyProductCard($chatId, $product);
    }

    protected function replyProductCard(string $chatId, Product $product): JsonResponse
    {
        $imageUrl = $product->image_url;
        $finalPrice = number_format((float) ($product->discount_price ?? $product->price), 2);
        $originalPrice = $product->discount_price !== null
            ? '$' . number_format((float) $product->price, 2)
            : null;

        $caption = "🛍 {$product->name}\n\n";
        $caption .= "💰 Price: \${$finalPrice}\n";
        if ($originalPrice) {
            $caption .= "Original price: {$originalPrice}\n";
        }
        $caption .= "📦 Stock: " . ($product->in_stock ? 'In stock' : 'Out of stock') . "\n";
        $caption .= "📝 SKU: {$product->sku}\n\n";
        $caption .= "Description:\n" . trim((string) $product->description) . "\n\n";
        $caption .= "_To order, scan this card or tap Order._";

        $replyMarkup = TelegramBotService::productCardKeyboard((string) $product->id, (bool) $product->in_stock);

        $result = $this->telegramBotService->sendPhoto($chatId, $imageUrl, $caption, $replyMarkup);

        if (!($result['ok'] ?? false)) {
            $this->telegramBotService->sendMessage(
                $chatId,
                "🛍 {$product->name}\n"
                    . "💰 Price: \${$finalPrice}\n"
                    . ($originalPrice ? "Original price: {$originalPrice}\n" : '')
                    . "📦 Stock: " . ($product->in_stock ? 'In stock' : 'Out of stock') . "\n"
                    . "📝 SKU: {$product->sku}\n\n"
                    . "Description:\n" . trim((string) $product->description) . "\n\n"
                    . "_To order, contact admin directly._",
                $replyMarkup
            );
        }

        return response()->json(['ok' => true]);
    }

    protected function handleTrack(string $chatId, string $text): JsonResponse
    {
        $parts = explode(' ', $text, 2);
        $orderId = trim((string) ($parts[1] ?? ''));

        if ($orderId === '' || !ctype_digit($orderId)) {
            $this->telegramBotService->sendMessage(
                $chatId,
                "Use /track with an order ID.\nExample:\n/track 123",
                TelegramBotService::orderTrackingKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        $order = Order::query()->find((int) $orderId);

        if (!$order) {
            $this->telegramBotService->sendMessage(
                $chatId,
                "Order #{$orderId} not found.",
                TelegramBotService::orderTrackingKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        $user = User::where('telegram_chat_id', $chatId)->first();
        if ($user && $order->user_id !== $user->id) {
            $this->telegramBotService->sendMessage(
                $chatId,
                "Order #{$orderId} exists, but it's not linked to your account.",
                TelegramBotService::orderTrackingKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        $shipping = json_decode((string) $order->shipping_address, true);
        $statusText = $order->status;
        $paymentStatus = $order->payment_status;
        $createdAt = optional($order->created_at)->format('Y-m-d H:i');

        $message = "📦 Order #{$order->id}\n"
            . "Order Number: {$order->order_number}\n"
            . "Status: {$statusText}\n"
            . "Payment: {$paymentStatus}\n"
            . "Total: \$" . number_format((float) $order->total, 2) . "\n"
            . "Created: {$createdAt}\n";

        if (!empty($shipping)) {
            $message .= "Shipping: " . trim((string) ($shipping['address'] ?? '')) . "\n";
        }

        $this->telegramBotService->sendMessage(
            $chatId,
            $message,
            TelegramBotService::orderTrackingKeyboard()
        );

        return response()->json(['ok' => true]);
    }

    protected function handleTelegramLogin(string $chatId, string $text): JsonResponse
    {
        $parts = explode(' ', $text, 3);
        $email = trim((string) ($parts[1] ?? ''));
        $password = trim((string) ($parts[2] ?? ''));

        if ($email === '' || $password === '') {
            $this->telegramBotService->sendMessage(
                $chatId,
                "Use /login with your ShopSphere credentials.\nExample:\n/login user@example.com yourPassword",
                TelegramBotService::accountLinkKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        $credentials = Validator::make(
            ['email' => $email, 'password' => $password],
            [
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
            ]
        );

        if ($credentials->fails()) {
            $this->telegramBotService->sendMessage(
                $chatId,
                "Invalid login format.\nExample:\n/login user@example.com yourPassword",
                TelegramBotService::accountLinkKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        try {
            $user = User::where('email', $email)->first();

            if (!$user || !Hash::check($password, $user->password)) {
                $this->telegramBotService->sendMessage(
                    $chatId,
                    "Login failed. Please check your email and password.\n\n"
                        . "Tip: send /help to get help or /status to check connection.",
                    TelegramBotService::accountLinkKeyboard()
                );

                return response()->json(['ok' => true]);
            }

            $user->telegram_chat_id = $chatId;
            $user->save();

            $tokenResult = $user->createToken('telegram-' . $chatId);
            $plainToken = $tokenResult->accessToken->id . '|' . $tokenResult->plainTextToken;

            $this->telegramBotService->sendMessage(
                $chatId,
                "✅ Login successful.\n"
                    . "User: {$user->name}\n"
                    . "Email: {$user->email}\n"
                    . "Telegram has been linked to your ShopSphere account.\n\n"
                    . "You can now use:\n"
                    . "/orders - recent orders\n"
                    . "/track <order_id> - track order\n"
                    . "/report <message> - chat with admin\n\n"
                    . "API Token: {$plainToken}",
                TelegramBotService::mainMenuKeyboard()
            );

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            Log::warning('Telegram login failed', ['error' => $e->getMessage()]);

            $this->telegramBotService->sendMessage(
                $chatId,
                "Login error. Please try again later.",
                TelegramBotService::accountLinkKeyboard()
            );

            return response()->json(['ok' => true]);
        }
    }

    public function generateToken(Request $request): JsonResponse
    {
        $user = $request->user();

        $token = Str::random(32);
        cache()->put("telegram_verify_{$token}", $user->id, now()->addMinutes(30));

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'bot_username' => config('services.telegram.bot_username', 'ShopSphereBot'),
                'instructions' => "Send this message to the bot:\n/start {$token}",
            ],
        ]);
    }

    public function connect(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id' => ['required', 'string'],
        ]);

        $user = $request->user();

        $existing = User::where('telegram_chat_id', $request->input('chat_id'))
            ->where('id', '!=', $user->id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This Telegram chat is already connected to another account.',
            ], 422);
        }

        $user->telegram_chat_id = $request->input('chat_id');
        $user->save();

        $this->telegramBotService->sendMessage(
            $request->input('chat_id'),
            "✅ Telegram connected to your account."
        );

        return response()->json([
            'success' => true,
            'message' => 'Telegram connected successfully.',
        ]);
    }

    public function disconnect(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->telegram_chat_id) {
            $this->telegramBotService->sendMessage(
                $user->telegram_chat_id,
                "👋 Telegram disconnected from ShopSphere.\nYou will no longer receive notifications here."
            );
        }

        $user->telegram_chat_id = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Telegram disconnected successfully.',
        ]);
    }

    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'connected' => !empty($user->telegram_chat_id),
                'telegram_chat_id' => $user->telegram_chat_id,
                'bot_username' => config('services.telegram.bot_username', 'ShopSphereBot'),
            ],
        ]);
    }

    protected function handleAccountLinking(string $chatId, string $firstName, string $username): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if ($user) {
            $this->telegramBotService->sendMessage(
                $chatId,
                "👋 Welcome back, {$user->name}!\n\n"
                    . "Your Telegram is already linked to this ShopSphere account.\n"
                    . "Commands:\n"
                    . "/report <message> - send support message\n"
                    . "/status - check connection\n"
                    . "/help - all commands\n"
                    . "/faq - frequently asked questions\n"
                    . "/orders - recent orders\n"
                    . "/track <order_id> - track an order\n"
                    . "/search <query> - search products",
                TelegramBotService::mainMenuKeyboard()
            );
        } else {
            $this->telegramBotService->sendMessage(
                $chatId,
                "👋 Hello {$firstName}!\n\n"
                    . "This bot provides support contact for ShopSphere.\n"
                    . "Use:\n"
                    . "/report <message> - chat with admin\n"
                    . "/help - commands\n"
                    . "/status - check connection\n"
                    . "/faq - frequently asked questions\n"
                    . "/orders - recent orders\n"
                    . "/track <order_id> - track an order\n"
                    . "/search <query> - search products\n\n"
                    . "To access full account features, use:\n"
                    . "/login <email> <password>",
                TelegramBotService::accountLinkKeyboard()
            );
        }
    }

    protected function handleStatus(string $chatId): JsonResponse
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if ($user) {
            $this->telegramBotService->sendMessage(
                $chatId,
                "✅ Connected as: {$user->name}\n"
                    . "📧 Email: {$user->email}\n"
                    . "👤 Role: {$user->role}",
                TelegramBotService::mainMenuKeyboard()
            );
        } else {
            $this->telegramBotService->sendMessage(
                $chatId,
                "❌ This chat is not connected to any ShopSphere account.\n\n"
                    . "You can verify via:\n"
                    . "• website connection flow\n"
                    . "• /login email password",
                TelegramBotService::accountLinkKeyboard()
            );
        }

        return response()->json(['ok' => true]);
    }

    protected function handleOrders(string $chatId, ?int $fromId): JsonResponse
    {
        if (!$fromId) {
            $this->telegramBotService->sendMessage($chatId, 'Please use /start, /id, or /login to link your account first.');

            return response()->json(['ok' => true]);
        }

        $user = User::where('telegram_chat_id', $chatId)->first();

        if (!$user) {
            return $this->handleStatus($chatId);
        }

        $orders = Order::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get(['id', 'status', 'total', 'created_at']);

        if ($orders->isEmpty()) {
            $this->telegramBotService->sendMessage($chatId, 'No orders found.', TelegramBotService::ordersKeyboard());

            return response()->json(['ok' => true]);
        }

        $lines = ['📦 *Recent Orders*'];
        foreach ($orders as $order) {
            $value = (float) ($order->total ?? 0);
            $lines[] = "Order #{$order->id} - {$order->status} - \${$value}";
        }

        $this->telegramBotService->sendMessage($chatId, implode("\n", $lines), TelegramBotService::ordersKeyboard());

        return response()->json(['ok' => true]);
    }

    protected function handleProductList(string $chatId): JsonResponse
    {
        $products = Product::query()
            ->where('status', true)
            ->latest()
            ->limit(8)
            ->get(['id', 'name', 'price', 'discount_price']);

        if ($products->isEmpty()) {
            $this->telegramBotService->sendMessage($chatId, 'No products available.', TelegramBotService::productSearchKeyboard());

            return response()->json(['ok' => true]);
        }

        $lines = ['🛍 Featured products:'];
        foreach ($products as $product) {
            $finalPrice = number_format((float) ($product->discount_price ?? $product->price), 2);
            $lines[] = "{$product->name}\nPrice: \${$finalPrice}\n/product {$product->id}";
        }

        $this->telegramBotService->sendMessage($chatId, implode("\n\n", $lines), TelegramBotService::productSearchKeyboard());

        return response()->json(['ok' => true]);
    }

    protected function handleAdminReply(string $text): JsonResponse
    {
        $adminChatId = config('services.telegram.chat_id');
        if (!$adminChatId) {
            return response()->json(['ok' => true]);
        }

        if (!str_starts_with($text, '/reply ')) {
            $this->telegramBotService->sendMessage($adminChatId, 'Use /reply <conversation_id> <message>');

            return response()->json(['ok' => true]);
        }

        $parts = explode(' ', $text, 3);
        $conversationId = $parts[1] ?? null;
        $messageBody = $parts[2] ?? '';

        if (!$conversationId || $messageBody === '') {
            $this->telegramBotService->sendMessage($adminChatId, 'Use /reply <conversation_id> <message>');

            return response()->json(['ok' => true]);
        }

        $conversation = SupportConversation::query()->find((int) $conversationId);

        if (!$conversation) {
            $this->telegramBotService->sendMessage($adminChatId, "Conversation #{$conversationId} not found.");

            return response()->json(['ok' => true]);
        }

        SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'sender_name' => $adminChatId,
            'message' => $messageBody,
        ]);

        $recipient = $conversation->telegram_chat_id
            ?? $conversation->user->telegram_chat_id
            ?? $adminChatId;

        $this->telegramBotService->sendMessage((string) $recipient, $messageBody);

        return response()->json(['ok' => true]);
    }

    protected function listConversations(string $chatId): JsonResponse
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        $conversations = SupportConversation::query()
            ->when($user, fn ($query) => $query->where('user_id', $user->id))
            ->when(! $user, fn ($query) => $query->where('telegram_chat_id', $chatId))
            ->latest('id')
            ->take(10)
            ->get(['id', 'status', 'source', 'customer_name', 'customer_email']);

        if ($conversations->isEmpty()) {
            $this->telegramBotService->sendMessage($chatId, 'You have no support conversations yet.');

            return response()->json(['ok' => true]);
        }

        $lines = ['Conversations:'];
        foreach ($conversations as $c) {
            $lines[] = "#{$c->id}";
        }

        $this->telegramBotService->sendMessage($chatId, implode("\n", $lines));

        return response()->json(['ok' => true]);
    }

    protected function closeConversation(string $text): JsonResponse
    {
        $parts = explode(' ', $text, 2);
        $conversationId = $parts[1] ?? null;

        if (!$conversationId) {
            return response()->json(['ok' => false], 422);
        }

        $conversation = SupportConversation::query()->find((int) $conversationId);

        if (!$conversation) {
            return response()->json(['ok' => true]);
        }

        $conversation->update(['status' => 'closed', 'closed_at' => now()]);
        $chatId = $conversation->telegram_chat_id ?? $conversation->user?->telegram_chat_id;

        if ($chatId) {
            $this->telegramBotService->sendMessage((string) $chatId, "Conversation #{$conversation->id} is closed.");
        }

        return response()->json(['ok' => true]);
    }

    protected function notifyTelegramAdmin(SupportConversation $conversation, string $message): void
    {
        $adminChatId = config('services.telegram.chat_id');

        if (!$adminChatId) {
            return;
        }

        $recipient = $conversation->user?->name
            ?? $conversation->customer_name
            ?? $conversation->customer_email;

        $text = "New support message\nConversation #{$conversation->id}\nFrom: {$recipient}\n\n{$message}"
            . "\n/close {$conversation->id}";

        $this->telegramBotService->sendMessage((string) $adminChatId, $text);
    }

    protected function resolveSupportConversation(string $chatId, string $firstName, string $username, ?int $ticketId): SupportConversation
    {
        $conversation = SupportConversation::query()
            ->where('telegram_chat_id', $chatId)
            ->where('status', 'open')
            ->latest('id')
            ->first();

        if ($conversation) {
            return $conversation;
        }

        $user = $this->prepareSupportUser($chatId, $firstName, $username);
        [$conversation] = $this->findOrStartConversation($user, $chatId);

        return $conversation;
    }

    /**
     * @return array{0: SupportConversation, 1: bool}
     */
    private function findOrStartConversation(User $user, string $chatId): array
    {
        $conversation = SupportConversation::query()
            ->where('source', 'telegram')
            ->where('status', 'open')
            ->where(function ($query) use ($user, $chatId) {
                $query->where('telegram_chat_id', $chatId)
                    ->orWhere('user_id', $user->id);
            })
            ->latest('id')
            ->first();

        if ($conversation) {
            return [$conversation, false];
        }

        if (empty($user->telegram_chat_id)) {
            $user->update(['telegram_chat_id' => $chatId]);
        }

        $conversation = SupportConversation::create([
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'status' => 'open',
            'source' => 'telegram',
            'telegram_chat_id' => $chatId,
        ]);

        return [$conversation, true];
    }

    private function prepareSupportUser(string $chatId, string $firstName, string $username): User
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if ($user) {
            return $user;
        }

        $user = User::where('telegram_user_id', $chatId)->first();

        if ($user) {
            return $user;
        }

        $identifier = $username ?: $chatId;
        $email = str_replace('@', '', $identifier) . '@telegram.local';

        $user = User::create([
            'name' => $firstName,
            'email' => $email,
            'password' => bcrypt($identifier),
            'role' => User::ROLE_CUSTOMER,
            'status' => User::STATUS_ACTIVE,
            'telegram_chat_id' => $chatId,
        ]);

        return $user;
    }

    protected function handleCallbackQuery(array $callbackQuery): JsonResponse
    {
        $chatId = (string) $callbackQuery['message']['chat']['id'];
        $data = (string) $callbackQuery['data'];
        $callbackQueryId = (string) $callbackQuery['id'];

        $this->telegramBotService->sendRequest('answerCallbackQuery', [
            'callback_query_id' => $callbackQueryId,
            'text' => 'Selected: ' . ucwords(str_replace(['_', '-'], ' ', $data)),
            'show_alert' => false,
        ]);

        if ($data === 'orders_recent') {
            return $this->handleOrders($chatId, array_key_exists('id', $callbackQuery['from'] ?? []) ? $callbackQuery['from']['id'] : null);
        }

        if ($data === 'orders_pending') {
            $this->telegramBotService->sendMessage(
                $chatId,
                "If you need a detailed pending payment report, please confirm your linked account or contact support directly.",
                TelegramBotService::ordersKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        if ($data === 'orders_status') {
            $this->telegramBotService->sendMessage(
                $chatId,
                "Use /status to see your connection status, or /report <message> to message support.",
                TelegramBotService::ordersKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        if ($data === 'help') {
            $mode = $this->normalizeBotMode(config('services.telegram.bot_mode'));
            $this->telegramBotService->sendMessage(
                $chatId,
                $this->modeHelp($mode),
                TelegramBotService::mainMenuKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        if (str_starts_with($data, 'orders_recent_')) {
            return $this->handleOrders($chatId, array_key_exists('id', $callbackQuery['from'] ?? []) ? $callbackQuery['from']['id'] : null);
        }

        if ($data === 'search_prompt') {
            $this->telegramBotService->sendMessage(
                $chatId,
                "Tell me what you want to search for.\nExample:\n/search jacket",
                TelegramBotService::productSearchKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        if ($data === 'faq_support') {
            $this->telegramBotService->sendMessage(
                $chatId,
                "To message support, use:\n/report <message>\n\nExample:\n/report My order is delayed.",
                TelegramBotService::faqKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        if ($data === 'login_prompt') {
            $this->telegramBotService->sendMessage(
                $chatId,
                "To link your ShopSphere account, use:\n/login <email> <password>\n\nExample:\n/login user@example.com yourPassword",
                TelegramBotService::accountLinkKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        if ($data === 'products_all') {
            return $this->handleProductList($chatId);
        }

        if (str_starts_with($data, 'product_')) {
            return $this->resolveProductCardCallback($chatId, $data);
        }

        if (str_starts_with($data, 'close_')) {
            return $this->closeConversation('/close ' . str_replace('close_', '', $data));
        }

        $this->telegramBotService->sendMessage(
            $chatId,
            "Button action not recognized.\nType /help to see available commands.",
            TelegramBotService::mainMenuKeyboard()
        );

        return response()->json(['ok' => true]);
    }

    protected function resolveProductCardCallback(string $chatId, string $data): JsonResponse
    {
        if ($data === 'product_order_' || $data === 'product_pay_') {
            $prefix = $data;
        } elseif (str_starts_with($data, 'product_order_') || str_starts_with($data, 'product_pay_')) {
            $prefix = str_starts_with($data, 'product_order_') ? 'product_order_' : 'product_pay_';
        } else {
            $prefix = 'product_';
        }

        if (str_starts_with($data, 'product_order_') || str_starts_with($data, 'product_pay_')) {
            return $this->handleProductAction($chatId, $data, str_starts_with($data, 'product_pay_'));
        }

        $productId = preg_replace('~^product_~', '', $data) ?? '';
        $parts = explode('_', (string) $productId, 2);
        $first = $parts[0] ?? '';

        if ($first !== '' && ctype_digit($first)) {
            return $this->handleProductCard($chatId, '/product ' . $first);
        }

        $this->telegramBotService->sendMessage(
            $chatId,
            "Please use /product <id> to view details.",
            TelegramBotService::productSearchKeyboard()
        );

        return response()->json(['ok' => true]);
    }

    protected function handleProductAction(string $chatId, string $data, bool $paymentInquiry): JsonResponse
    {
        $productId = preg_replace('~^(product_order_|product_pay_)~', '', $data) ?? '';

        if ($productId === '' || !ctype_digit($productId)) {
            $this->telegramBotService->sendMessage(
                $chatId,
                "Product not found for this action.\nUse /product {$productId} to retry.",
                TelegramBotService::productSearchKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        $product = Product::query()->find((int) $productId);

        if (!$product || !$product->status) {
            $this->telegramBotService->sendMessage(
                $chatId,
                "Product #{$productId} not found or unavailable.\nUse /search <query> to find another product.",
                TelegramBotService::productSearchKeyboard()
            );

            return response()->json(['ok' => true]);
        }

        $finalPrice = number_format((float) ($product->discount_price ?? $product->price), 2);
        $action = $paymentInquiry ? 'Ask for pay' : 'Order';

        $this->telegramBotService->sendMessage(
            (string) config('services.telegram.chat_id'),
            "🛒 New {$action}\n"
                . "Product: {$product->name}\n"
                . "Price: \${$finalPrice}\n"
                . "Telegram chat: {$chatId}\n"
                . "SKU: {$product->sku}\n"
                . "Stock: " . ($product->in_stock ? 'In stock' : 'Out of stock'),
            TelegramBotService::productCardKeyboard((string) $product->id, (bool) $product->in_stock)
        );

        $this->telegramBotService->sendMessage(
            $chatId,
            "✅ Your {$action} request for `{$product->name}` was sent to support with price `\${$finalPrice}`. We will contact you here with next steps.",
            TelegramBotService::productCardKeyboard((string) $product->id, (bool) $product->in_stock)
        );

        return response()->json(['ok' => true]);
    }

    private function normalizeBotMode(?string $mode): string
    {
        $mode = strtolower((string) $mode);

        return in_array($mode, ['notification', 'both'], true) ? $mode : 'support';
    }

    private function modeHelp(string $mode): string
    {
        $common = "/start - begin\n"
            . "/status - connection status\n"
            . "/help - this menu\n"
            . "/id - show chat id\n";

        $support = "/report <message> - chat with admin\n"
            . "/support - chat with admin\n"
            . "/faq - frequently asked questions\n";

        $notify = "/orders - recent orders\n"
            . "/track <order_id> - track an order\n"
            . "/search <query> - search products\n"
            . "/login email password - link existing account\n";

        if ($mode === 'notification') {
            return "ℹ️ *Notification Bot Commands*\n" . $common . $notify;
        }

        if ($mode === 'both') {
            return "ℹ️ *Bot Commands*\n" . $common . $support . $notify;
        }

        return "ℹ️ *Support Bot Commands*\n" . $common . $support;
    }
}

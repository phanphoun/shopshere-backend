<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();

        // Create or resume conversation for the session
        $sessionId = $request->input('session_id') ?? (string) Str::random(40);
        $conversation = SupportConversation::where('source', 'web')
            ->where('status', 'open')
            ->when($user, fn($q) => $q->where('user_id', $user->id))
            ->when(!$user, fn($q) => $q->where('session_id', $sessionId))
            ->latest('id')
            ->first();

        if (!$conversation) {
            $conversation = SupportConversation::create([
                'user_id' => $user?->id,
                'session_id' => !$user ? $sessionId : null,
                'customer_name' => $user?->name ?? $request->input('name'),
                'customer_email' => $user?->email ?? $request->input('email'),
                'source' => 'web',
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'conversation_id' => $conversation->id,
                'session_id' => $sessionId,
            ],
        ]);
    }

    public function messages(Request $request, $id): JsonResponse
    {
        $conversation = SupportConversation::findOrFail($id);
        $messages = $conversation->messages()
            ->orderBy('created_at')
            ->get(['id', 'sender_type', 'sender_name', 'message', 'created_at']);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'conversation_id' => ['required', 'exists:support_conversations,id'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversation = SupportConversation::findOrFail($validated['conversation_id']);
        $user = Auth::guard('sanctum')->user();

        $message = SupportMessage::create([
            'support_conversation_id' => $conversation->id,
            'sender_type' => 'customer',
            'sender_name' => $user?->name ?? $conversation->customer_name ?? 'Guest',
            'message' => $validated['message'],
        ]);

        // LAST Resort scope: if there is a connected Telegram admin, notify them
        $this->notifyTelegramAdmin($conversation, $message);

        return response()->json([
            'success' => true,
            'data' => $message,
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        $conversations = SupportConversation::query()
            ->when($user, fn($q) => $q->where('user_id', $user->id))
            ->when(!$user, fn($q) => $q->where('session_id', $request->input('session_id')))
            ->latest()
            ->get(['id', 'status', 'created_at'])
            ->map(fn($c) => [
                'id' => $c->id,
                'status' => $c->status,
                'last_message_at' => optional($c->messages()->latest()->first())->created_at,
            ]);

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }

    private function notifyTelegramAdmin(SupportConversation $conversation, SupportMessage $message): void
    {
        $adminChatId = config('services.telegram.chat_id');
        if (!$adminChatId) {
            return;
        }

        $recipient = $conversation->user?->name ?? $conversation->customer_email ?? $conversation->session_id;
        $text = "*New Support Message*\n"
            . "Conversation #{$conversation->id}\n"
            . "From: {$recipient}\n\n"
            . $message->message;

        try {
            app(\App\Services\TelegramBotService::class)
                ->sendMessage((string) $adminChatId, $text);
        } catch (\Throwable $e) {
            \Log::warning('Support telegram notification failed: '.$e->getMessage());
        }
    }
}

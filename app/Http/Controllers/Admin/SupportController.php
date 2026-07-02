<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportConversation;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $query = SupportConversation::query()->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $conversations = $query->paginate(20);

        return view('admin.support.index', compact('conversations'));
    }

    public function show(SupportConversation $conversation): View
    {
        $conversation->load(['messages', 'user']);
        $conversation->messages()->whereNull('read_at')->update(['read_at' => now()]);

        return view('admin.support.show', compact('conversation'));
    }

    public function reply(Request $request, SupportConversation $conversation): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $conversation->messages()->create([
            'sender_type' => 'admin',
            'sender_name' => Auth::user()?->name ?? 'Admin',
            'message' => $validated['message'],
        ]);

        // Notify telegram if enabled
        try {
            $recipient = $conversation->user?->telegram_chat_id ?? config('services.telegram.chat_id');
            if ($recipient) {
                app(\App\Services\TelegramBotService::class)
                    ->sendMessage((string) $recipient, "Admin replied to conversation #{$conversation->id}:\n\n" . $validated['message']);
            }
        } catch (\Throwable $e) {
            \Log::warning('Admin support reply telegram notification failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Reply sent successfully.');
    }

    public function close(Request $request, SupportConversation $conversation): \Illuminate\Http\RedirectResponse
    {
        $conversation->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return back()->with('success', 'Conversation closed.');
    }

    public function reopen(Request $request, SupportConversation $conversation): \Illuminate\Http\RedirectResponse
    {
        $conversation->update([
            'status' => 'open',
            'closed_at' => null,
        ]);

        return back()->with('success', 'Conversation reopened.');
    }
}

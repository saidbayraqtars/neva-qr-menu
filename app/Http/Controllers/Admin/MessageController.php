<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Admin tarafı destek mesajları — kullanıcıya yazdığımız yanıtlar. */
class MessageController extends Controller
{
    public function __construct(private readonly MessagingService $messaging) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->value() ?: 'open';

        $conversations = Conversation::with(['user', 'restaurant'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('unread_for_admin')
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.messages.index', compact('conversations', 'status'));
    }

    public function show(Conversation $conversation): View
    {
        $this->messaging->markRead($conversation, Message::ROLE_ADMIN);

        return view('admin.messages.show', [
            'conversation' => $conversation->load(['messages.sender', 'user', 'restaurant']),
        ]);
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $this->messaging->post($conversation, $request->user(), Message::ROLE_ADMIN, $validated['body']);

        return back()->with('success', 'Yanıtınız kullanıcının paneline iletildi.');
    }

    public function toggleStatus(Conversation $conversation): RedirectResponse
    {
        $conversation->forceFill([
            'status' => $conversation->isOpen() ? Conversation::STATUS_CLOSED : Conversation::STATUS_OPEN,
        ])->save();

        return back()->with('success', $conversation->isOpen() ? 'Görüşme yeniden açıldı.' : 'Görüşme kapatıldı.');
    }

    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        $after = (int) $request->query('after', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $after)
            ->get()
            ->map(fn (Message $m) => [
                'id' => $m->id,
                'role' => $m->sender_role,
                'body' => $m->body,
                'at' => $m->created_at?->format('d.m.Y H:i'),
            ]);

        if ($messages->isNotEmpty()) {
            $this->messaging->markRead($conversation, Message::ROLE_ADMIN);
        }

        return response()->json(['messages' => $messages]);
    }
}

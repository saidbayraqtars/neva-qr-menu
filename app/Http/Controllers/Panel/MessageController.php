<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\MessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * İşletme sahibinin destek mesajları (panel tarafı).
 * Admin cevap yazdığında bu ekranda anında görünür (5 sn polling).
 */
class MessageController extends Controller
{
    public function __construct(private readonly MessagingService $messaging) {}

    public function index(Request $request): View
    {
        $conversations = Conversation::where('user_id', $request->user()->id)
            ->withCount('messages')
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('panel.messages.index', compact('conversations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $conversation = $this->messaging->startForUser(
            $request->user(),
            app()->bound('restaurant') ? app('restaurant') : null,
            $validated['subject'],
            $validated['body'],
        );

        return redirect()
            ->route('panel.messages.show', $conversation)
            ->with('success', 'Mesajınız iletildi. Yanıtımız bu ekranda görünecek.');
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorizeOwner($request, $conversation);

        $this->messaging->markRead($conversation, Message::ROLE_USER);

        return view('panel.messages.show', [
            'conversation' => $conversation->load('messages.sender'),
        ]);
    }

    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorizeOwner($request, $conversation);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $this->messaging->post($conversation, $request->user(), Message::ROLE_USER, $validated['body']);

        return back()->with('success', 'Mesajınız gönderildi.');
    }

    /** Canlı akış — istemci 5 saniyede bir yeni mesajları çeker. */
    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorizeOwner($request, $conversation);

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
            $this->messaging->markRead($conversation, Message::ROLE_USER);
        }

        return response()->json(['messages' => $messages]);
    }

    private function authorizeOwner(Request $request, Conversation $conversation): void
    {
        abort_unless($conversation->user_id === $request->user()->id, 403);
    }
}

<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\SupportMessagePosted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

/**
 * Panel içi çift yönlü mesajlaşma.
 * Okunmamış sayaçları burada tek noktadan yönetilir.
 */
class MessagingService
{
    public function startForUser(User $user, ?Restaurant $restaurant, string $subject, string $body): Conversation
    {
        return DB::transaction(function () use ($user, $restaurant, $subject, $body) {
            $conversation = Conversation::create([
                'user_id' => $user->id,
                'restaurant_id' => $restaurant?->id,
                'subject' => $subject,
                'status' => Conversation::STATUS_OPEN,
            ]);

            $this->post($conversation, $user, Message::ROLE_USER, $body);

            return $conversation->fresh();
        });
    }

    /** Bir mesaj ekler, sayaçları günceller, karşı tarafa bildirim yollar. */
    public function post(Conversation $conversation, User $sender, string $role, string $body): Message
    {
        $message = DB::transaction(function () use ($conversation, $sender, $role, $body) {
            $message = $conversation->messages()->create([
                'sender_id' => $sender->id,
                'sender_role' => $role,
                'body' => $body,
            ]);

            $conversation->forceFill([
                'last_message_at' => now(),
                'last_sender_role' => $role,
                'status' => Conversation::STATUS_OPEN,
            ]);

            if ($role === Message::ROLE_ADMIN) {
                $conversation->unread_for_user = $conversation->unread_for_user + 1;
            } else {
                $conversation->unread_for_admin = $conversation->unread_for_admin + 1;
            }

            $conversation->save();

            return $message;
        });

        $this->notifyCounterparty($conversation, $message);

        return $message;
    }

    /** Görüşmeyi açan taraf okudu → o tarafın sayacı sıfırlanır. */
    public function markRead(Conversation $conversation, string $role): void
    {
        $column = $role === Message::ROLE_ADMIN ? 'unread_for_admin' : 'unread_for_user';

        if ((int) $conversation->{$column} === 0) {
            return;
        }

        $conversation->forceFill([$column => 0])->save();

        $conversation->messages()
            ->whereNull('read_at')
            ->where('sender_role', '!=', $role)
            ->update(['read_at' => now()]);
    }

    public function unreadForUser(User $user): int
    {
        return (int) Conversation::where('user_id', $user->id)->sum('unread_for_user');
    }

    public function unreadForAdmin(): int
    {
        return (int) Conversation::sum('unread_for_admin');
    }

    private function notifyCounterparty(Conversation $conversation, Message $message): void
    {
        try {
            if ($message->isFromAdmin()) {
                $conversation->user?->notify(new SupportMessagePosted($conversation, $message));

                return;
            }

            $admins = User::where('role', User::ROLE_ADMIN)->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new SupportMessagePosted($conversation, $message));
            }
        } catch (\Throwable $e) {
            report($e); // bildirim gitmemesi mesajlaşmayı bozmasın
        }
    }
}

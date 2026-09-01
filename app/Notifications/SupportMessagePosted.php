<?php

namespace App\Notifications;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Karşı tarafa yeni mesaj bildirimi.
 * Admin yazdıysa kullanıcıya, kullanıcı yazdıysa admine gider.
 */
class SupportMessagePosted extends Notification
{
    use Queueable;

    public function __construct(
        public Conversation $conversation,
        public Message $message,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $fromAdmin = $this->message->isFromAdmin();

        $url = $fromAdmin
            ? route('panel.messages.show', $this->conversation)
            : route('admin.messages.show', $this->conversation);

        return (new MailMessage)
            ->subject(($fromAdmin ? 'Destek ekibinden yanıt' : 'Yeni destek mesajı').' — '.$this->conversation->subject)
            ->greeting('Merhaba,')
            ->line('"'.$this->conversation->subject.'" konulu görüşmede yeni bir mesaj var:')
            ->line('"'.\Illuminate\Support\Str::limit($this->message->body, 300).'"')
            ->action('Mesajı aç', $url);
    }
}

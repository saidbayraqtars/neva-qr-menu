<?php

namespace App\Notifications;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/** Ziyaretçi iletişim formu → destek ekibine bildirim. */
class ContactMessageReceived extends Notification implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public ContactMessage $contactMessage) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $m = $this->contactMessage;

        return (new MailMessage)
            ->subject('Yeni iletişim formu — '.$m->name)
            ->line('**Ad:** '.$m->name)
            ->line('**E-posta:** '.$m->email)
            ->line('**Telefon:** '.($m->phone ?: '—'))
            ->line('**Konu:** '.($m->subject ?: '—'))
            ->line('**Mesaj:** '.Str::limit($m->body, 500))
            ->action('Admin panelinde aç', route('admin.contact.index'));
    }
}

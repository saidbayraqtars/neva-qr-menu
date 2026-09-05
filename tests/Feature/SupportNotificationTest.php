<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\SupportMessagePosted;
use App\Services\MessagingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Destek bildirimi admine iletişim bilgisi taşımalı — destek çoğu zaman
 * telefonla çözülüyor, admin panele girmeden arayabilmeli.
 */
class SupportNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function scenario(): array
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $owner = User::factory()->create([
            'role' => User::ROLE_OWNER,
            'name' => 'Ayşe Yılmaz',
            'phone' => '0555 123 45 67',
        ]);
        $restaurant = Restaurant::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Lumina Bistro',
        ]);

        return [$admin, $owner, $restaurant];
    }

    public function test_admin_notification_carries_the_customer_phone(): void
    {
        [$admin, $owner, $restaurant] = $this->scenario();

        $conversation = app(MessagingService::class)
            ->startForUser($owner, $restaurant, 'QR kodum çalışmıyor', 'Masadaki kod açılmıyor.');

        $mail = (new SupportMessagePosted($conversation, $conversation->messages->first()))
            ->toMail($admin);

        $rendered = implode("\n", $mail->introLines);

        $this->assertStringContainsString('Lumina Bistro', $rendered);
        $this->assertStringContainsString('Ayşe Yılmaz', $rendered);
        $this->assertStringContainsString('0555 123 45 67', $rendered);
        // tel: bağlantısı boşluksuz olmalı, yoksa telefon uygulaması açılmaz.
        $this->assertStringContainsString('tel:05551234567', $rendered);
        $this->assertStringContainsString('QR kodum çalışmıyor', $rendered);
    }

    /** Müşteriye giden bildirimde admin tarafının bilgileri OLMAMALI. */
    public function test_customer_notification_stays_minimal(): void
    {
        [$admin, $owner, $restaurant] = $this->scenario();

        $messaging = app(MessagingService::class);
        $conversation = $messaging->startForUser($owner, $restaurant, 'Soru', 'Merhaba');
        $reply = $messaging->post($conversation, $admin, \App\Models\Message::ROLE_ADMIN, 'Yardımcı olalım');

        $rendered = implode("\n", (new SupportMessagePosted($conversation, $reply))->toMail($owner)->introLines);

        $this->assertStringNotContainsString('Telefon', $rendered);
        $this->assertStringNotContainsString('0555', $rendered);
    }

    public function test_admins_are_notified_when_a_customer_writes(): void
    {
        Notification::fake();
        [$admin, $owner, $restaurant] = $this->scenario();

        app(MessagingService::class)->startForUser($owner, $restaurant, 'Konu', 'Mesaj gövdesi');

        Notification::assertSentTo($admin, SupportMessagePosted::class);
    }

    /** Numara kayıtlı değilse bildirim yine gitmeli, çökmemeli. */
    public function test_missing_phone_does_not_break_the_notification(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $owner = User::factory()->create(['role' => User::ROLE_OWNER, 'phone' => null]);
        $restaurant = Restaurant::factory()->create(['user_id' => $owner->id, 'phone' => null]);

        $conversation = app(MessagingService::class)->startForUser($owner, $restaurant, 'K', 'M');

        $rendered = implode("\n", (new SupportMessagePosted($conversation, $conversation->messages->first()))
            ->toMail($admin)->introLines);

        $this->assertStringContainsString('kayıtlı değil', $rendered);
    }
}

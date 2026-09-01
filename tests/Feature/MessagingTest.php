<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MessagingTest extends TestCase
{
    use RefreshDatabase;

    public function test_kullanici_konu_acabilir(): void
    {
        Notification::fake();
        [$user] = $this->ownerWithPlan();

        $this->actingAs($user)
            ->post(route('panel.messages.store'), [
                'subject' => 'Paket yükseltme',
                'body' => 'Alt domain paketine geçmek istiyorum.',
            ])
            ->assertRedirect();

        $conversation = Conversation::first();

        $this->assertSame('Paket yükseltme', $conversation->subject);
        $this->assertSame(1, $conversation->unread_for_admin);
        $this->assertSame(0, $conversation->unread_for_user);
    }

    public function test_admin_yaniti_kullanicinin_panelinde_gorunur(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        [$user] = $this->ownerWithPlan();

        $this->actingAs($user)->post(route('panel.messages.store'), [
            'subject' => 'Soru',
            'body' => 'Merhaba',
        ]);

        $conversation = Conversation::first();

        $this->actingAs($admin)
            ->post(route('admin.messages.reply', $conversation), ['body' => 'Merhaba, yardımcı olalım.'])
            ->assertRedirect();

        $conversation->refresh();
        $this->assertSame(1, $conversation->unread_for_user);
        $this->assertSame(Message::ROLE_ADMIN, $conversation->last_sender_role);

        $this->actingAs($user)
            ->get(route('panel.messages.show', $conversation))
            ->assertOk()
            ->assertSee('Merhaba, yardımcı olalım.');

        // Görüntüleyince okunmamış sayacı sıfırlanır
        $this->assertSame(0, $conversation->fresh()->unread_for_user);
    }

    public function test_polling_yalnizca_yeni_mesajlari_dondurur(): void
    {
        Notification::fake();
        $admin = User::factory()->admin()->create();
        [$user] = $this->ownerWithPlan();

        $this->actingAs($user)->post(route('panel.messages.store'), [
            'subject' => 'Soru', 'body' => 'İlk mesaj',
        ]);

        $conversation = Conversation::first();
        $firstId = $conversation->messages()->max('id');

        $this->actingAs($admin)->post(route('admin.messages.reply', $conversation), ['body' => 'Yanıt']);

        $this->actingAs($user)
            ->getJson(route('panel.messages.poll', ['conversation' => $conversation, 'after' => $firstId]))
            ->assertOk()
            ->assertJsonCount(1, 'messages')
            ->assertJsonPath('messages.0.body', 'Yanıt');
    }

    public function test_baska_kullanicinin_konusu_okunamaz(): void
    {
        Notification::fake();
        [$user] = $this->ownerWithPlan();
        [$other] = $this->ownerWithPlan();

        $this->actingAs($user)->post(route('panel.messages.store'), [
            'subject' => 'Gizli', 'body' => 'Özel bilgi',
        ]);

        $conversation = Conversation::first();

        $this->actingAs($other)
            ->get(route('panel.messages.show', $conversation))
            ->assertForbidden();
    }

    public function test_giris_yapmamis_ziyaretci_iletisim_formu_kaydedilir(): void
    {
        Notification::fake();

        $this->post(route('contact'), [
            'name' => 'Ahmet Yılmaz',
            'email' => 'ahmet@example.com',
            'message' => 'Fiyatlar hakkında bilgi almak istiyorum.',
        ])->assertRedirect();

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'ahmet@example.com',
            'status' => 'new',
        ]);
    }

    public function test_bot_tuzagi_dolduruldugunda_kayit_olusmaz(): void
    {
        $this->post(route('contact'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'spam spam spam',
            'website_url' => 'http://spam.example',
        ])->assertRedirect();

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_giris_yapmis_kullanicidan_ad_soyad_istenmez(): void
    {
        [$user] = $this->ownerWithPlan();

        $this->actingAs($user)
            ->get(route('contact'))
            ->assertOk()
            ->assertDontSee('name="email"', false)
            ->assertSee('Merhaba '.$user->name);
    }
}

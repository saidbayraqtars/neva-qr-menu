<?php

namespace Tests\Feature;

use App\Models\MembershipRequest;
use App\Models\Plan;
use App\Models\User;
use App\Notifications\AccountReady;
use App\Services\MembershipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Düz metin geçici şifre saklamanın yerine geçen akış.
 */
class PasswordSetupTest extends TestCase
{
    use RefreshDatabase;

    public function test_uyelik_onayi_sifre_belirleme_linki_gonderir_ve_duz_metin_saklamaz(): void
    {
        Notification::fake();

        $admin = User::factory()->admin()->create();
        $plan = Plan::create([
            'name' => 'Hosting Dahil', 'slug' => 'hosting-dahil', 'price' => 6000,
            'interval' => 'once', 'is_active' => true, 'sort_order' => 1,
        ]);

        $request = MembershipRequest::create([
            'business_name' => 'Galya Cafe',
            'name' => 'Mehmet',
            'email' => 'mehmet@example.com',
            'plan_id' => $plan->id,
            'amount' => 6000,
            'reference_code' => 'NQR-TEST01',
            'status' => MembershipRequest::STATUS_PENDING,
            'payment_status' => MembershipRequest::PAYMENT_PAID,
            'paid_at' => now(),
        ]);

        app(MembershipService::class)->approve($request, $admin->id);

        $user = User::firstWhere('email', 'mehmet@example.com');

        $this->assertNotNull($user);
        $this->assertTrue($user->mustChangePassword());
        $this->assertNotNull($user->password_setup_token);

        // Jeton yalnız hash olarak saklanır (64 karakterlik sha256)
        $this->assertSame(64, strlen($user->password_setup_token));

        Notification::assertSentTo($user, AccountReady::class);
    }

    public function test_odeme_isaretlenmeden_onay_verilemez(): void
    {
        $admin = User::factory()->admin()->create();

        $request = MembershipRequest::create([
            'business_name' => 'Galya Cafe',
            'name' => 'Mehmet',
            'email' => 'mehmet@example.com',
            'amount' => 6000,
            'reference_code' => 'NQR-TEST02',
            'status' => MembershipRequest::STATUS_PENDING,
            'payment_status' => MembershipRequest::PAYMENT_UNPAID,
        ]);

        $this->actingAs($admin)
            ->from(route('admin.memberships.index'))
            ->post(route('admin.memberships.approve', $request))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('users', ['email' => 'mehmet@example.com']);
    }

    public function test_gecerli_jetonla_sifre_belirlenir(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);
        $token = $user->issuePasswordSetupToken();

        $this->post(route('password.setup.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'YeniSifre123!',
            'password_confirmation' => 'YeniSifre123!',
        ])->assertRedirect(route('panel.dashboard'));

        $user->refresh();

        $this->assertFalse($user->mustChangePassword());
        $this->assertNull($user->password_setup_token);
        $this->assertTrue(auth()->check());
    }

    public function test_jeton_tek_kullanimliktir(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);
        $token = $user->issuePasswordSetupToken();

        $this->post(route('password.setup.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'YeniSifre123!',
            'password_confirmation' => 'YeniSifre123!',
        ]);

        auth()->logout();

        $this->post(route('password.setup.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'BaskaSifre123!',
            'password_confirmation' => 'BaskaSifre123!',
        ])->assertSessionHasErrors('token');
    }

    public function test_suresi_dolmus_jeton_reddedilir(): void
    {
        $user = User::factory()->create(['must_change_password' => true]);
        $token = $user->issuePasswordSetupToken();

        $user->forceFill(['password_setup_expires_at' => now()->subMinute()])->save();

        $this->post(route('password.setup.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'YeniSifre123!',
            'password_confirmation' => 'YeniSifre123!',
        ])->assertSessionHasErrors('token');
    }

    public function test_kayit_basvurusu_havale_referans_kodu_uretir(): void
    {
        Notification::fake();

        $plan = Plan::create([
            'name' => 'Hosting Dahil', 'slug' => 'hosting-dahil', 'price' => 6000,
            'interval' => 'once', 'is_active' => true, 'sort_order' => 1,
        ]);

        $this->post(route('register'), [
            'business_name' => 'Galya Cafe',
            'name' => 'Mehmet',
            'email' => 'mehmet@example.com',
            'plan_id' => $plan->id,
        ])->assertRedirect(route('register.received'));

        $request = MembershipRequest::firstWhere('email', 'mehmet@example.com');

        $this->assertNotNull($request);
        $this->assertStringStartsWith('NQR-', $request->reference_code);
        $this->assertSame('unpaid', $request->payment_status);

        // Hesap HENÜZ açılmaz — ödeme onayı beklenir.
        $this->assertDatabaseMissing('users', ['email' => 'mehmet@example.com']);
    }
}

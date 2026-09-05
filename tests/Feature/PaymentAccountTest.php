<?php

namespace Tests\Feature;

use App\Models\PaymentAccount;
use App\Models\User;
use App\Support\PaymentAccounts;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Havale hesapları — para akışını belirleyen ekran.
 * Yanlış IBAN doğrudan gelir kaybı, boş liste ise kayıt akışının kırılması demek.
 */
class PaymentAccountTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'bank_name' => 'Ziraat Bankası',
            'account_name' => 'Said Bayraktar',
            'iban' => 'TR33 0006 1005 1978 6457 8413 26',
            'is_active' => '1',
        ], $overrides);
    }

    public function test_only_admins_can_manage_accounts(): void
    {
        $this->get(route('admin.accounts.index'))->assertRedirect(route('login'));

        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $this->actingAs($owner)->get(route('admin.accounts.index'))->assertForbidden();
        $this->actingAs($owner)->post(route('admin.accounts.store'), $this->payload())->assertForbidden();
    }

    public function test_admin_can_add_an_account(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.accounts.store'), $this->payload())
            ->assertRedirect();

        // IBAN boşluksuz ve büyük harf saklanmalı.
        $this->assertDatabaseHas('payment_accounts', [
            'bank_name' => 'Ziraat Bankası',
            'iban' => 'TR330006100519786457841326',
        ]);
    }

    /** Sağlama hanesi tutmayan IBAN reddedilmeli — tek hane hatası parayı kaybettirir. */
    public function test_invalid_iban_is_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.accounts.store'), $this->payload(['iban' => 'TR33 0006 1005 1978 6457 8413 27']))
            ->assertSessionHasErrors('iban');

        $this->assertDatabaseCount('payment_accounts', 0);
    }

    public function test_iban_checksum_helper(): void
    {
        $this->assertTrue(PaymentAccount::ibanIsValid('TR33 0006 1005 1978 6457 8413 26'));
        $this->assertFalse(PaymentAccount::ibanIsValid('TR33 0006 1005 1978 6457 8413 27'));
        $this->assertFalse(PaymentAccount::ibanIsValid('DE89 3704 0044 0532 0130 00'));  // TR degil
        $this->assertFalse(PaymentAccount::ibanIsValid('TR12'));                          // kisa
    }

    /** Aynı IBAN boşluklu yazılsa bile ikinci kez eklenememeli. */
    public function test_duplicate_iban_is_rejected_regardless_of_spacing(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.accounts.store'), $this->payload());

        $this->actingAs($admin)
            ->post(route('admin.accounts.store'), $this->payload(['iban' => 'TR330006100519786457841326']))
            ->assertSessionHasErrors('iban');

        $this->assertDatabaseCount('payment_accounts', 1);
    }

    public function test_hidden_accounts_are_not_offered_to_customers(): void
    {
        PaymentAccount::create($this->payload(['is_active' => false]));

        $this->assertTrue(PaymentAccounts::active()->isEmpty());
    }

    /** Tablo boşken .env'deki tek hesaba düşmeli — geriye dönük uyumluluk. */
    public function test_it_falls_back_to_the_env_account(): void
    {
        config(['neva.payment.bank' => [
            'account_name' => 'Eski Hesap',
            'bank_name' => 'Vakıfbank',
            'iban' => 'TR33 0006 1005 1978 6457 8413 26',
        ]]);

        $accounts = PaymentAccounts::active();

        $this->assertCount(1, $accounts);
        $this->assertSame('Vakıfbank', $accounts->first()->bank_name);
    }

    /** Örnek/boş IBAN müşteriye ASLA gösterilmemeli. */
    public function test_placeholder_env_iban_is_not_shown(): void
    {
        config(['neva.payment.bank' => [
            'account_name' => 'Neva',
            'bank_name' => 'Ziraat',
            'iban' => 'TR00 0000 0000 0000 0000 0000 00',
        ]]);

        $this->assertTrue(PaymentAccounts::active()->isEmpty());
    }

    public function test_customer_sees_every_active_account_after_registering(): void
    {
        PaymentAccount::create($this->payload());
        PaymentAccount::create($this->payload([
            'bank_name' => 'İş Bankası',
            'iban' => 'TR68 0006 4000 0011 2345 6789 01',
        ]));

        $plan = \App\Models\Plan::create([
            'name' => 'Test Paketi',
            'slug' => 'test-paketi',
            'price' => 3000,
            'currency' => 'TRY',
            'interval' => 'once',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->post(route('register'), [
            'business_name' => 'Lumina',
            'name' => 'Ayşe',
            'email' => 'ayse@example.com',
            'plan_id' => $plan->id,
        ])->assertRedirect(route('register.received'));

        $this->followingRedirects()
            ->get(route('register.received'))
            ->assertSee('Ziraat Bankası')
            ->assertSee('İş Bankası')
            ->assertSee('Hesaplardan herhangi birine');
    }
}

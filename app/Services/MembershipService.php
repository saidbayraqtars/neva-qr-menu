<?php

namespace App\Services;

use App\Models\MembershipRequest;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\AccountReady;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Üyelik / hesap sağlama.
 *
 * GÜVENLİK: geçici şifre artık HİÇBİR YERDE düz metin saklanmaz. Hesap açılınca
 * kullanıcıya tek kullanımlık, süreli "şifre belirle" linki e-posta ile gider.
 * Şifreyi yalnızca kullanıcı bilir; admin göremez.
 */
class MembershipService
{
    /**
     * Üyelik talebini onayla: hesabı aç, şifre belirleme linkini gönder, aboneliği başlat.
     * Talep zaten onaylanmışsa hiçbir şey yapmaz.
     */
    public function approve(MembershipRequest $request, int $adminId): MembershipRequest
    {
        if ($request->status === MembershipRequest::STATUS_APPROVED) {
            return $request;
        }

        $user = DB::transaction(function () use ($request, $adminId) {
            $user = $this->provision(
                email: $request->email,
                name: $request->name,
                businessName: $request->business_name,
                planId: $request->plan_id,
                amount: (float) $request->amount,
                phone: $request->phone,
            );

            $request->update([
                'status' => MembershipRequest::STATUS_APPROVED,
                'payment_status' => MembershipRequest::PAYMENT_PAID,
                'paid_at' => $request->paid_at ?? now(),
                'user_id' => $user->id,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
            ]);

            return $user;
        });

        $this->sendSetupLink($user);

        return $request->fresh(['plan', 'user']);
    }

    /**
     * Admin'in ELLE oluşturduğu hesap — üyelik talebi olmadan, doğrudan aktif.
     */
    public function createAccount(string $businessName, string $name, string $email, ?int $planId, ?string $phone = null): User
    {
        $plan = $planId ? Plan::find($planId) : null;

        $user = DB::transaction(fn () => $this->provision(
            email: $email,
            name: $name,
            businessName: $businessName,
            planId: $plan?->id,
            amount: (float) ($plan?->price ?? 0),
            phone: $phone,
        ));

        $this->sendSetupLink($user);

        return $user;
    }

    /** Kullanıcıya yeni şifre belirleme linki gönderir (eski linki geçersiz kılar). */
    public function sendSetupLink(User $user): void
    {
        $token = $user->issuePasswordSetupToken();

        $user->notify(new AccountReady($token));
    }

    public function reject(MembershipRequest $request, int $adminId, ?string $note = null): void
    {
        $request->update([
            'status' => MembershipRequest::STATUS_REJECTED,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'admin_note' => $note,
        ]);
    }

    /**
     * User(owner) + Restaurant(draft) + Subscription(active) yaratır/günceller.
     * Şifre rastgele atanır ve KİMSEYE gösterilmez; kullanıcı linkle belirler.
     */
    private function provision(string $email, string $name, string $businessName, ?int $planId, float $amount, ?string $phone = null): User
    {
        $user = User::firstOrNew(['email' => Str::lower(trim($email))]);

        $user->fill([
            'name' => $name,
            'role' => User::ROLE_OWNER,
            'phone' => $phone ?? $user->phone,
            'must_change_password' => true,
        ]);

        // Kullanılamaz rastgele şifre — giriş yalnızca "şifre belirle" linkiyle açılır.
        $user->password = Hash::make(Str::random(64));
        $user->save();

        $restaurant = $user->restaurants()->first() ?? $user->restaurants()->forceCreate([
            'name' => $businessName,
            'slug' => $this->uniqueRestaurantSlug($businessName),
            'template' => Restaurant::DEFAULT_TEMPLATE,
            'status' => Restaurant::STATUS_DRAFT,
            'menu_version' => 1,
        ]);

        Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $planId,
                'restaurant_id' => $restaurant->id,
                'status' => Subscription::STATUS_ACTIVE,
                'amount' => $amount,
                'currency' => 'TRY',
                'interval' => 'once',
                'provider' => 'bank_transfer',
                'current_period_starts_at' => now(),
                'current_period_ends_at' => null,
            ]
        );

        return $user;
    }

    private function uniqueRestaurantSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'isletme';
        $slug = $base;
        $i = 2;

        while (Restaurant::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}

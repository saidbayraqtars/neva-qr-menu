<?php

namespace App\Services;

use App\Models\MembershipRequest;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MembershipService
{
    /**
     * Üyelik talebini onayla: hesabı aktif et, geçici şifre üret, aboneliği başlat.
     * Talep zaten onaylanmışsa hiçbir şey yapmaz.
     */
    public function approve(MembershipRequest $request, int $adminId): MembershipRequest
    {
        if ($request->status === MembershipRequest::STATUS_APPROVED) {
            return $request;
        }

        return DB::transaction(function () use ($request, $adminId) {
            [$user, $tempPassword] = $this->provision(
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
                'user_id' => $user->id,
                'temp_password' => $tempPassword,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
            ]);

            return $request->fresh(['plan', 'user']);
        });
    }

    /**
     * Admin'in ELLE oluşturduğu hesap — üyelik talebi olmadan.
     * Doğrudan aktif; geçici şifre + zorunlu değişim bayrağı ile.
     *
     * @return array{0: User, 1: string}  [user, tempPassword]
     */
    public function createAccount(string $businessName, string $name, string $email, ?int $planId, ?string $phone = null): array
    {
        $plan = $planId ? Plan::find($planId) : null;

        return DB::transaction(fn () => $this->provision(
            email: $email,
            name: $name,
            businessName: $businessName,
            planId: $plan?->id,
            amount: (float) ($plan?->price ?? 0),
            phone: $phone,
        ));
    }

    /**
     * User(owner) + Restaurant(draft) + Subscription(active) yaratır/günceller,
     * güçlü geçici şifre atar. Tekrar çağrılırsa aynı e-postadaki hesabı günceller.
     *
     * @return array{0: User, 1: string}
     */
    private function provision(string $email, string $name, string $businessName, ?int $planId, float $amount, ?string $phone = null): array
    {
        $tempPassword = $this->generateTempPassword();

        $user = User::firstOrNew(['email' => Str::lower(trim($email))]);
        $user->fill([
            'name' => $name,
            'role' => User::ROLE_OWNER,
            'phone' => $phone ?? $user->phone,
            'password' => Hash::make($tempPassword),
            'temp_password' => $tempPassword,
            'must_change_password' => true,
        ])->save();

        $restaurant = $user->restaurants()->first() ?? $user->restaurants()->create([
            'name' => $businessName,
            'slug' => $this->uniqueRestaurantSlug($businessName),
            'template' => Restaurant::DEFAULT_TEMPLATE,
            'status' => Restaurant::STATUS_DRAFT,
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
                'provider' => 'manual',
                'current_period_starts_at' => now(),
                'current_period_ends_at' => null,
            ]
        );

        return [$user, $tempPassword];
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

    /** Herhangi bir kullanıcıya yeni geçici şifre ver + ilk girişte değiştirmeye zorla. */
    public function resetPassword(User $user): string
    {
        $temp = $this->generateTempPassword();

        $user->update([
            'password' => Hash::make($temp),
            'temp_password' => $temp,
            'must_change_password' => true,
        ]);

        return $temp;
    }

    public function generateTempPassword(): string
    {
        // Okunası ama güçlü: 3 harf-öbeği + rakam, karışık büyük/küçük.
        return Str::title(Str::random(4)).'-'.Str::lower(Str::random(4)).'-'.random_int(100, 999);
    }

    private function uniqueRestaurantSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'isletme';
        $slug = $base;
        $i = 2;

        while (Restaurant::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}

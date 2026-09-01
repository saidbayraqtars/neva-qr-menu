<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\SubdomainRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubdomainService
{
    public function normalize(string $value): string
    {
        return (string) Str::of($value)->lower()->trim()->replaceMatches('/[^a-z0-9-]/', '');
    }

    /**
     * @throws ValidationException
     */
    public function assertAvailable(string $label, ?int $ignoreRestaurantId = null): void
    {
        $label = $this->normalize($label);

        if (! preg_match(config('neva.subdomain_pattern'), $label)) {
            throw ValidationException::withMessages([
                'requested_subdomain' => 'Alt domain 3-32 karakter olmalı; küçük harf, rakam ve tire içerebilir.',
            ]);
        }

        if (in_array($label, config('neva.reserved_subdomains'), true)) {
            throw ValidationException::withMessages([
                'requested_subdomain' => "“{$label}” ayrılmış bir addır, lütfen başka bir ad seçin.",
            ]);
        }

        $taken = Restaurant::where('subdomain', $label)
            ->when($ignoreRestaurantId, fn ($q) => $q->whereKeyNot($ignoreRestaurantId))
            ->exists();

        $pending = SubdomainRequest::where('requested_subdomain', $label)
            ->where('status', SubdomainRequest::STATUS_PENDING)
            ->when($ignoreRestaurantId, fn ($q) => $q->where('restaurant_id', '!=', $ignoreRestaurantId))
            ->exists();

        if ($taken || $pending) {
            throw ValidationException::withMessages([
                'requested_subdomain' => "“{$label}” şu anda kullanılamıyor.",
            ]);
        }
    }

    public function request(Restaurant $restaurant, string $label): SubdomainRequest
    {
        $label = $this->normalize($label);
        $this->assertAvailable($label, $restaurant->id);

        // Aynı restoran için bekleyen eski talebi kapat.
        $restaurant->subdomainRequests()
            ->where('status', SubdomainRequest::STATUS_PENDING)
            ->update(['status' => SubdomainRequest::STATUS_REJECTED, 'admin_note' => 'Yeni talep ile değiştirildi.']);

        return $restaurant->subdomainRequests()->create([
            'user_id' => $restaurant->user_id,
            'requested_subdomain' => $label,
            'status' => SubdomainRequest::STATUS_PENDING,
        ]);
    }

    public function approve(SubdomainRequest $request, int $adminId): void
    {
        $this->assertAvailable($request->requested_subdomain, $request->restaurant_id);

        $request->update([
            'status' => SubdomainRequest::STATUS_APPROVED,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
        ]);

        $request->restaurant->update([
            'subdomain' => $request->requested_subdomain,
            'status' => Restaurant::STATUS_APPROVED,
            'approved_at' => now(),
            'published_at' => now(),
            'rejection_reason' => null,
        ]);

        // Alt domain açıldı → ANA İŞLETME QR'ını otomatik üret.
        try {
            app(QrService::class)->storeMainForRestaurant($request->restaurant->fresh());
        } catch (\Throwable $e) {
            report($e); // QR üretimi menünün yayınlanmasını engellemesin
        }
    }

    public function reject(SubdomainRequest $request, int $adminId, ?string $note): void
    {
        $request->update([
            'status' => SubdomainRequest::STATUS_REJECTED,
            'reviewed_by' => $adminId,
            'reviewed_at' => now(),
            'admin_note' => $note,
        ]);

        $request->restaurant->update([
            'status' => Restaurant::STATUS_REJECTED,
            'rejection_reason' => $note,
        ]);
    }
}

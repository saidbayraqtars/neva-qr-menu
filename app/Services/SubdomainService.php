<?php

namespace App\Services;

use App\Jobs\PublishSubdomain;
use App\Models\Restaurant;
use App\Models\SubdomainRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SubdomainService
{
    /** Sonuç kodları — anlık kontrol endpoint'i bunları döner. */
    public const OK = 'ok';
    public const INVALID = 'invalid';
    public const RESERVED = 'reserved';
    public const TAKEN = 'taken';

    public function normalize(string $value): string
    {
        return (string) Str::of($value)
            ->lower()
            ->trim()
            ->replaceMatches('/[\s_.]+/', '-')     // boşluk / alt çizgi / nokta → tire
            ->replaceMatches('/[^a-z0-9-]/', '')   // kalan geçersiz karakterleri at
            ->replaceMatches('/-+/', '-')          // ardışık tireleri sadeleştir
            ->trim('-');
    }

    /**
     * Anlık müsaitlik kontrolü — istisna FIRLATMAZ, durum döner.
     * Panelde kullanıcı yazarken çağrılır.
     *
     * @return array{status: string, label: string, message: string, available: bool}
     */
    public function availability(string $raw, ?int $ignoreRestaurantId = null): array
    {
        $label = $this->normalize($raw);

        if ($label === '' || ! preg_match(config('neva.subdomain_pattern'), $label)) {
            return $this->result(self::INVALID, $label,
                'Alt domain 3-32 karakter olmalı; yalnızca küçük harf, rakam ve tire içerebilir.');
        }

        if (in_array($label, config('neva.reserved_subdomains'), true)) {
            return $this->result(self::RESERVED, $label,
                "\u{201C}{$label}\u{201D} sistem tarafından ayrılmış bir addır. Lütfen farklı bir alt domain adı girin.");
        }

        if ($this->isTaken($label, $ignoreRestaurantId)) {
            return $this->result(self::TAKEN, $label,
                "\u{201C}{$label}\u{201D} başka bir işletme tarafından alınmış. Lütfen farklı bir alt domain adı girin.");
        }

        return $this->result(self::OK, $label,
            "\u{201C}{$label}.".config('neva.root_domain')."\u{201D} müsait.");
    }

    /**
     * @throws ValidationException
     */
    public function assertAvailable(string $rawLabel, ?int $ignoreRestaurantId = null): string
    {
        $result = $this->availability($rawLabel, $ignoreRestaurantId);

        if (! $result['available']) {
            throw ValidationException::withMessages([
                'requested_subdomain' => $result['message'],
            ]);
        }

        return $result['label'];
    }

    public function request(Restaurant $restaurant, string $rawLabel): SubdomainRequest
    {
        $label = $this->assertAvailable($rawLabel, $restaurant->id);

        return DB::transaction(function () use ($restaurant, $label) {
            // Aynı restoran için bekleyen eski talebi kapat (partial unique index'i de serbest bırakır).
            $restaurant->subdomainRequests()
                ->where('status', SubdomainRequest::STATUS_PENDING)
                ->update([
                    'status' => SubdomainRequest::STATUS_REJECTED,
                    'admin_note' => 'Yeni talep ile değiştirildi.',
                ]);

            return $restaurant->subdomainRequests()->create([
                'user_id' => $restaurant->user_id,
                'requested_subdomain' => $label,
                'status' => SubdomainRequest::STATUS_PENDING,
            ]);
        });
    }

    /**
     * Admin onayı → alt domain yazılır, restoran yayına alınır ve
     * PublishSubdomain işi kuyruğa atılır (DNS + otomatik doğrulama).
     */
    public function approve(SubdomainRequest $request, int $adminId): void
    {
        $label = $this->assertAvailable($request->requested_subdomain, $request->restaurant_id);

        DB::transaction(function () use ($request, $adminId, $label) {
            $request->update([
                'status' => SubdomainRequest::STATUS_APPROVED,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
            ]);

            $request->restaurant->forceFill([
                'subdomain' => $label,
                'status' => Restaurant::STATUS_APPROVED,
                'approved_at' => now(),
                'published_at' => now(),
                'rejection_reason' => null,
                'publish_status' => Restaurant::PUBLISH_QUEUED,
                'publish_error' => null,
            ])->save();
        });

        PublishSubdomain::dispatch($request->restaurant_id);
    }

    public function reject(SubdomainRequest $request, int $adminId, ?string $note): void
    {
        DB::transaction(function () use ($request, $adminId, $note) {
            $request->update([
                'status' => SubdomainRequest::STATUS_REJECTED,
                'reviewed_by' => $adminId,
                'reviewed_at' => now(),
                'admin_note' => $note,
            ]);

            $request->restaurant->forceFill([
                'status' => Restaurant::STATUS_REJECTED,
                'rejection_reason' => $note,
            ])->save();
        });
    }

    private function isTaken(string $label, ?int $ignoreRestaurantId): bool
    {
        $taken = Restaurant::withTrashed()
            ->where('subdomain', $label)
            ->when($ignoreRestaurantId, fn ($q) => $q->whereKeyNot($ignoreRestaurantId))
            ->exists();

        $pending = SubdomainRequest::where('requested_subdomain', $label)
            ->where('status', SubdomainRequest::STATUS_PENDING)
            ->when($ignoreRestaurantId, fn ($q) => $q->where('restaurant_id', '!=', $ignoreRestaurantId))
            ->exists();

        return $taken || $pending;
    }

    private function result(string $status, string $label, string $message): array
    {
        return [
            'status' => $status,
            'label' => $label,
            'message' => $message,
            'available' => $status === self::OK,
        ];
    }
}

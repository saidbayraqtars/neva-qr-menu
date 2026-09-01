<?php

namespace App\Services;

use App\Models\Restaurant;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Paket bazlı yetki matrisi — SUNUCU TARAFI zorlama.
 *
 * View'lerdeki @if'ler yalnızca görsel; gerçek engel burada. Kullanıcı doğrudan
 * POST atsa bile paketinde olmayan özelliği kullanamaz.
 *
 * Matris: config/neva.php › plan_features (anahtar = plans.slug).
 */
class PlanGate
{
    /** Paket bu yeteneği içeriyor mu? */
    public function allows(Restaurant $restaurant, string $feature): bool
    {
        return $restaurant->planAllows($feature);
    }

    /** İçermiyorsa 403 — insan diliyle mesajla. */
    public function authorize(Restaurant $restaurant, string $feature): void
    {
        if ($this->allows($restaurant, $feature)) {
            return;
        }

        throw new HttpException(403, $this->denialMessage($feature));
    }

    /**
     * Sayısal limit kontrolü (kategori/ürün/masa adedi).
     * $adding: eklenmek istenen adet.
     */
    public function authorizeLimit(Restaurant $restaurant, string $key, int $current, int $adding = 1, string $field = 'limit'): void
    {
        $limit = $restaurant->planLimit($key);

        if ($limit === null) {
            return; // sınırsız
        }

        if ($current + $adding > $limit) {
            throw ValidationException::withMessages([
                $field => $this->limitMessage($key, $limit),
            ]);
        }
    }

    public function denialMessage(string $feature): string
    {
        $label = config("neva.feature_labels.$feature", $feature);

        return "Bu özellik paketinizde yok: {$label}. Paketinizi yükseltmek için bizimle iletişime geçin.";
    }

    private function limitMessage(string $key, int $limit): string
    {
        $labels = [
            'categories' => 'kategori',
            'products' => 'ürün',
            'tables' => 'masa',
        ];
        $label = $labels[$key] ?? $key;

        if ($limit === 0) {
            return "Paketiniz {$label} eklemeye izin vermiyor. Paket yükseltmesi için bizimle iletişime geçin.";
        }

        return "Paketinizin {$label} sınırına ulaştınız (en fazla {$limit}). Paketinizi yükselterek sınırı kaldırabilirsiniz.";
    }
}

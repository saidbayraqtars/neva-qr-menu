<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\RestaurantTable;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

/**
 * QR üretimi. Çekirdek kodu endroid üretir; QrArtService etrafına dekoratif
 * çerçeve/desen çizer (10 tasarım — config/neva.php › qr_designs).
 *
 * Tüm public metotlar PNG BYTE dizesi döndürür.
 */
class QrService
{
    public function __construct(private QrArtService $art) {}

    /** ANA İŞLETME QR — alt domain köküne yönlenir. */
    public function forRestaurant(Restaurant $restaurant, int $size = 1000): string
    {
        return $this->render(tenant_domain($restaurant), $size, $restaurant);
    }

    /** Masaya özel QR — ana adrese `?masa=<etiket>` eklenmiş hâli. */
    public function forParam(Restaurant $restaurant, string $masa, int $size = 900): string
    {
        return $this->render(tenant_domain($restaurant, '/?masa='.rawurlencode($masa)), $size, $restaurant);
    }

    /** Kayıtlı bir masa için `?masa=<etiket>` QR'ı. */
    public function forTable(RestaurantTable $table, int $size = 900): string
    {
        return $this->forParam($table->restaurant, $table->label, $size);
    }

    /** "Hosting Hariç" paketi: kullanıcının girdiği dış menü adresine yönlenen bağımsız QR. */
    public function forExternalUrl(string $url, ?Restaurant $restaurant = null, int $size = 1000): string
    {
        return $this->render($url, $size, $restaurant);
    }

    /** Belirli bir tasarımın örnek önizlemesi (panelde kaydırarak seçim için). */
    public function preview(string $data, string $designKey, ?Restaurant $restaurant = null, int $size = 520): string
    {
        $designs = config('neva.qr_designs');
        $design = $designs[$designKey] ?? reset($designs);

        return $this->build($data, $size, $design, $restaurant);
    }

    /** Ana QR'ı diske yazar. */
    public function storeMainForRestaurant(Restaurant $restaurant): string
    {
        $path = "restaurants/{$restaurant->id}/qr/main.png";
        Storage::disk(config('neva.uploads.disk'))->put($path, $this->forRestaurant($restaurant));

        return $path;
    }

    public function render(string $data, int $size = 900, ?Restaurant $restaurant = null): string
    {
        $design = $restaurant
            ? $restaurant->qrDesignConfig()
            : config('neva.qr_designs.sade');

        return $this->build($data, $size, $design, $restaurant);
    }

    /** Tek üretim noktası: çekirdek QR + dekoratif çerçeve. */
    private function build(string $data, int $size, array $design, ?Restaurant $restaurant): string
    {
        $wantsLogo = ($design['logo'] ?? false) && $restaurant;
        $logo = $wantsLogo ? $this->logoFor($restaurant, $size) : null;

        $qrCode = new QrCode(
            data: $data,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: $size,
            margin: 28,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: $this->color($design['ink'] ?? '#0D0D07'),
            backgroundColor: $this->color($design['paper'] ?? '#FFFFFF'),
        );

        $qrImage = (new PngWriter)->write($qrCode, $logo)->getImage();

        return $this->art->compose($qrImage, $design, $design['label_text'] ?? 'Menü için okutun');
    }

    private function logoFor(Restaurant $restaurant, int $size): ?Logo
    {
        $disk = Storage::disk(config('neva.uploads.disk'));

        $path = ($restaurant->logo_path && $disk->exists($restaurant->logo_path))
            ? $disk->path($restaurant->logo_path)
            : public_path('img/nevalogo.png');

        if (! is_file($path)) {
            return null;
        }

        return new Logo(
            path: $path,
            resizeToWidth: (int) round($size * 0.2),
            punchoutBackground: true,
        );
    }

    private function color(string $hex): Color
    {
        [$r, $g, $b] = sscanf(ltrim($hex, '#'), '%02x%02x%02x');

        return new Color((int) $r, (int) $g, (int) $b);
    }
}

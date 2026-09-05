<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Yüklenen görselleri saklamadan ÖNCE küçültür ve yeniden kodlar.
 *
 * NEDEN: doğrulama 6 MB'a kadar izin veriyor ve dosya olduğu gibi saklanıyordu.
 * Müşteri telefonundan çektiği fotoğrafı yüklediğinde menüyü açan HER misafir
 * o 6 MB'ı indiriyordu — 20 ürünlü bir menü 120 MB ediyor. QR menü mobil veriyle
 * açılan bir üründe bu kabul edilemez.
 *
 * Sonuç: 6 MB'lık bir fotoğraf ~150-250 KB'a iniyor, gözle görülür kalite
 * kaybı olmadan. Orijinal SAKLANMAZ — disk bütçesi de menü hızı kadar önemli.
 *
 * WebP varsa tercih edilir (JPEG'den ~%30 küçük, her güncel tarayıcı destekler).
 * Şeffaflık gereken yerde (logo) PNG korunur.
 */
class ImageProcessor
{
    /** Kaynak görselde bundan çok piksel varsa GD'ye hiç yüklenmez. */
    private const MAX_SOURCE_PIXELS = 40_000_000;   // ~40 MP

    /**
     * Görseli işleyip diske yazar ve saklanan yolu döndürür.
     *
     * @param  int  $maxEdge  En uzun kenar bu değere indirilir (küçükse dokunulmaz).
     * @param  bool  $transparency  true ise PNG olarak yazılır (logo/şeffaf zemin).
     */
    public static function store(
        UploadedFile $file,
        string $directory,
        string $disk,
        int $maxEdge,
        bool $transparency = false,
    ): string {
        $processed = self::process($file, $maxEdge, $transparency);

        // İşlenemediyse (bozuk dosya, GD desteklemeyen format) orijinali sakla:
        // yükleme sessizce kaybolmasın.
        if ($processed === null) {
            return $file->store($directory, $disk);
        }

        [$binary, $extension] = $processed;
        $path = trim($directory, '/').'/'.Str::random(40).'.'.$extension;

        Storage::disk($disk)->put($path, $binary);

        return $path;
    }

    /**
     * @return array{0: string, 1: string}|null  [ikili içerik, uzantı] ya da işlenemezse null
     */
    private static function process(UploadedFile $file, int $maxEdge, bool $transparency): ?array
    {
        if (! function_exists('imagecreatefromstring')) {
            return null;
        }

        $info = @getimagesize($file->getRealPath());

        // Boyut okunamıyorsa ya da absürt büyükse GD'ye hiç verme: 40 MP'lik
        // bir görsel bellekte ~160 MB tutar ve süreci OOM'a sürükleyebilir.
        if ($info === false || ($info[0] * $info[1]) > self::MAX_SOURCE_PIXELS) {
            return null;
        }

        $binary = @file_get_contents($file->getRealPath());

        if ($binary === false) {
            return null;
        }

        $source = @imagecreatefromstring($binary);

        if ($source === false) {
            return null;
        }

        try {
            $width = imagesx($source);
            $height = imagesy($source);
            $scale = min(1, $maxEdge / max($width, $height));

            $target = $source;

            if ($scale < 1) {
                $newWidth = max(1, (int) round($width * $scale));
                $newHeight = max(1, (int) round($height * $scale));

                $target = imagecreatetruecolor($newWidth, $newHeight);

                if ($transparency) {
                    imagealphablending($target, false);
                    imagesavealpha($target, true);
                    imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
                }

                imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            } elseif ($transparency) {
                imagealphablending($target, false);
                imagesavealpha($target, true);
            }

            return self::encode($target, $transparency);
        } finally {
            imagedestroy($source);
            if (isset($target) && $target !== $source) {
                imagedestroy($target);
            }
        }
    }

    /** @return array{0: string, 1: string} */
    private static function encode(\GdImage $image, bool $transparency): array
    {
        // Şeffaflık gerekiyorsa PNG: WebP de şeffaflık destekler ama logo
        // küçük ve az renkli olduğu için PNG'de zaten küçük çıkıyor.
        if ($transparency) {
            ob_start();
            imagepng($image, null, 7);

            return [(string) ob_get_clean(), 'png'];
        }

        if (function_exists('imagewebp')) {
            ob_start();
            imagewebp($image, null, 82);
            $webp = (string) ob_get_clean();

            if ($webp !== '') {
                return [$webp, 'webp'];
            }
        }

        ob_start();
        imagejpeg($image, null, 82);

        return [(string) ob_get_clean(), 'jpg'];
    }
}

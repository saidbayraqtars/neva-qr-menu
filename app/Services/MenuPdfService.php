<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Support\TemplatePresenter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;

/**
 * Menü PDF'i — CANLI ŞABLONUN TA KENDİSİ.
 *
 * Ayrı bir PDF/HTML şablonu YOKTUR. `templates.show` görünümü `print` modunda
 * render edilir (aynı skeletons/*.blade.php + aynı derlenmiş app.css inline
 * gömülü), görseller data-URI'ye çevrilir ve headless Chrome/Edge ile
 * "--print-to-pdf" çağrılır. Böylece indirilen menü, web'dekiyle birebir aynıdır.
 */
class MenuPdfService
{
    public function build(Restaurant $restaurant): BinaryFileResponse
    {
        @ini_set('memory_limit', '512M');

        $restaurant->load([
            'categories' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            'categories.products' => fn ($q) => $q->where('is_available', true)->orderBy('sort_order'),
        ]);

        $html = view('templates.show', [
            'presenter' => new TemplatePresenter($restaurant),
            'restaurant' => $restaurant,
            'categories' => $restaurant->categories,
            'view' => 'phone',
            'embedded' => true,
            'print' => true,
            'tableLabel' => null,
        ])->render();

        $html = $this->inlineImages($html);

        // Proje AĞACININ DIŞINDA — Chrome profil kilitleri Vite watcher'ını çökertmesin.
        $dir = sys_get_temp_dir().'/neva-pdf';
        @mkdir($dir, 0775, true);
        $stub = $dir.'/menu-'.$restaurant->id.'-'.Str::random(8);
        $htmlFile = $stub.'.html';
        $pdfFile = $stub.'.pdf';
        file_put_contents($htmlFile, $html);

        try {
            $this->renderPdf($htmlFile, $pdfFile);
        } finally {
            @unlink($htmlFile);
        }

        if (! is_file($pdfFile) || filesize($pdfFile) < 800) {
            @unlink($pdfFile);
            throw new RuntimeException('PDF üretilemedi. Chrome/Edge çıktısı boş döndü.');
        }

        return response()
            ->download($pdfFile, $this->filename($restaurant), ['Content-Type' => 'application/pdf'])
            ->deleteFileAfterSend();
    }

    public function filename(Restaurant $restaurant): string
    {
        return Str::slug($restaurant->name).'-menu.pdf';
    }

    /* --------------------------------------------------------------------- */

    /** storage/ görsellerini data-URI'ye çevir (headless tarayıcı yerel sunucuya geri istek atmasın).
     *  Aynı dosya (ör. birçok karta düşen fallback logosu) YALNIZCA BİR KEZ kodlanır. */
    private function inlineImages(string $html): string
    {
        $disk = Storage::disk(config('neva.uploads.disk'));
        $base = rtrim($disk->url('/'), '/');            // ör. http://127.0.0.1:8000/storage
        $pattern = '#'.preg_quote($base, '#').'/([A-Za-z0-9._/\-]+\.(?:png|jpe?g|webp|gif|svg))#i';

        $cache = [];

        return preg_replace_callback($pattern, function ($m) use ($disk, &$cache) {
            $path = $m[1];
            if (array_key_exists($path, $cache)) {
                return $cache[$path] ?? $m[0];
            }
            $cache[$path] = null;
            if ($disk->exists($path)) {
                $cache[$path] = $this->encodeImage($disk->get($path), strtolower(pathinfo($path, PATHINFO_EXTENSION)));
            }

            return $cache[$path] ?? $m[0];
        }, $html);
    }

    /**
     * Görseli data-URI'ye çevir; büyükse küçült.
     * PNG kaynak → PNG kalır (şeffaflık korunur, logolar için kritik).
     * En uzun kenar 900px'e indirilir — PDF baskı için fazlasıyla yeterli, dosya küçük kalır.
     */
    private function encodeImage(string $bin, string $ext): ?string
    {
        $mime = ['jpg' => 'jpeg', 'jpeg' => 'jpeg', 'png' => 'png', 'webp' => 'webp', 'gif' => 'gif', 'svg' => 'svg+xml'][$ext] ?? 'png';
        $passthrough = fn () => "data:image/$mime;base64,".base64_encode($bin);

        if ($ext === 'svg' || ! function_exists('imagecreatefromstring')) {
            return $passthrough();
        }

        $src = @imagecreatefromstring($bin);
        if (! $src) {
            return $passthrough();
        }

        $sw = imagesx($src);
        $sh = imagesy($src);
        $max = 900;

        if (max($sw, $sh) <= $max && strlen($bin) < 90_000) {
            imagedestroy($src);

            return $passthrough();
        }

        $scale = min(1, $max / max($sw, $sh));
        $nw = max(1, (int) round($sw * $scale));
        $nh = max(1, (int) round($sh * $scale));
        $dst = imagecreatetruecolor($nw, $nh);

        $png = in_array($ext, ['png', 'webp', 'gif'], true);
        if ($png) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $sw, $sh);

        ob_start();
        if ($png) {
            imagepng($dst, null, 7);
            $type = 'png';
        } else {
            imagejpeg($dst, null, 82);
            $type = 'jpeg';
        }
        $out = ob_get_clean();
        imagedestroy($src);
        imagedestroy($dst);

        return "data:image/$type;base64,".base64_encode($out);
    }

    private function renderPdf(string $htmlFile, string $pdfFile): void
    {
        $chrome = $this->chromeBinary();
        $fileUrl = 'file:///'.str_replace('\\', '/', $htmlFile);
        $profile = dirname($htmlFile).'/prof-'.Str::random(6);
        @mkdir($profile, 0775, true);

        $process = new Process([
            $chrome,
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--no-first-run',
            '--no-default-browser-check',
            '--hide-scrollbars',
            '--force-color-profile=srgb',
            '--disable-dev-shm-usage',
            // Windows'ta web-sunucu bağlamından spawn edilince crashpad named-pipe hatası veriyor → tamamen kapat.
            '--disable-crash-reporter',
            '--disable-breakpad',
            '--disable-features=Crashpad,CrashpadHelper,MediaRouter',
            '--user-data-dir='.$profile,
            '--crash-dumps-dir='.$profile,
            '--run-all-compositor-stages-before-draw',
            '--virtual-time-budget=12000',
            '--no-pdf-header-footer',
            '--print-to-pdf-no-header',
            '--print-to-pdf='.$pdfFile,
            $fileUrl,
        ]);
        $process->setTimeout(70);
        // Temiz ortam: TEMP/HOME değişkenleri eksikse crashpad patlıyor.
        $process->setEnv([
            'TEMP' => sys_get_temp_dir(),
            'TMP' => sys_get_temp_dir(),
        ]);
        $process->run();

        $this->rrmdir($profile);

        if (! is_file($pdfFile)) {
            throw new RuntimeException('Headless tarayıcı PDF üretemedi: '.trim($process->getErrorOutput() ?: $process->getOutput()));
        }
    }

    private function rrmdir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) ?: [] as $f) {
            if ($f === '.' || $f === '..') {
                continue;
            }
            $p = $dir.DIRECTORY_SEPARATOR.$f;
            is_dir($p) ? $this->rrmdir($p) : @unlink($p);
        }
        @rmdir($dir);
    }

    /** Windows/macOS/Linux üzerinde Chrome ya da Edge (Chromium) ikili yolu. */
    private function chromeBinary(): string
    {
        if ($env = env('CHROME_BINARY')) {
            return $env;
        }

        $candidates = [
            'C:\Program Files\Google\Chrome\Application\chrome.exe',
            'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
            'C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe',
            'C:\Program Files\Microsoft\Edge\Application\msedge.exe',
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            '/Applications/Microsoft Edge.app/Contents/MacOS/Microsoft Edge',
            '/usr/bin/google-chrome',
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        throw new RuntimeException('PDF için Chrome veya Edge bulunamadı. .env dosyasına CHROME_BINARY yolunu ekleyin.');
    }
}

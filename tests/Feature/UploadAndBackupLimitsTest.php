<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Restaurant;
use App\Models\User;
use App\Support\ImageProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Yükleme küçültme ve yedek saklama sınırları.
 *
 * İkisi de "sessizce diski doldurma" sınıfı sorunlar: kimse şikâyet etmez,
 * bir gün disk dolar. Bu yüzden teste bağlı.
 */
class UploadAndBackupLimitsTest extends TestCase
{
    use RefreshDatabase;

    /** Büyük bir görsel saklanmadan önce küçültülmeli. */
    public function test_large_upload_is_downscaled_before_storage(): void
    {
        $disk = config('neva.uploads.disk');
        Storage::fake($disk);

        // 3000x2000 — tipik bir telefon fotoğrafı ölçüsü.
        $file = UploadedFile::fake()->image('buyuk.jpg', 3000, 2000);
        $original = $file->getSize();

        $path = ImageProcessor::store($file, 'restaurants/1/products', $disk, 1200);

        $this->assertTrue(Storage::disk($disk)->exists($path));

        $stored = Storage::disk($disk)->get($path);
        [$w, $h] = getimagesizefromstring($stored);

        $this->assertLessThanOrEqual(1200, max($w, $h), 'en uzun kenar sınırı aşıyor');
        $this->assertLessThan($original, strlen($stored), 'dosya küçülmemiş');
    }

    /** Zaten küçük bir görsel gereksiz yere büyütülmemeli. */
    public function test_small_image_is_not_upscaled(): void
    {
        $disk = config('neva.uploads.disk');
        Storage::fake($disk);

        $path = ImageProcessor::store(
            UploadedFile::fake()->image('kucuk.jpg', 400, 300),
            'restaurants/1/products', $disk, 1200
        );

        [$w, $h] = getimagesizefromstring(Storage::disk($disk)->get($path));

        $this->assertSame(400, $w);
        $this->assertSame(300, $h);
    }

    /** Logo şeffaflık için PNG olarak saklanmalı. */
    public function test_logo_is_stored_as_png(): void
    {
        $disk = config('neva.uploads.disk');
        Storage::fake($disk);

        $path = ImageProcessor::store(
            UploadedFile::fake()->image('logo.png', 900, 900),
            'restaurants/1/brand', $disk, 512, transparency: true
        );

        $this->assertStringEndsWith('.png', $path);
        [$w] = getimagesizefromstring(Storage::disk($disk)->get($path));
        $this->assertLessThanOrEqual(512, $w);
    }

    /** Ürün yüklemesi gerçekten küçültülmüş yolu kaydetmeli. */
    public function test_product_upload_goes_through_the_processor(): void
    {
        $disk = config('neva.uploads.disk');
        Storage::fake($disk);

        $owner = User::factory()->create(['role' => User::ROLE_OWNER]);
        $restaurant = Restaurant::factory()->create(['user_id' => $owner->id]);
        $category = Category::factory()->create(['restaurant_id' => $restaurant->id]);

        $this->actingAs($owner)->post(route('panel.products.store'), [
            'category_id' => $category->id,
            'name' => 'Test Ürün',
            'price' => 100,
            'image' => UploadedFile::fake()->image('foto.jpg', 2400, 1600),
        ])->assertRedirect();

        $product = $restaurant->products()->firstOrFail();
        $this->assertNotNull($product->image_path);

        [$w] = getimagesizefromstring(Storage::disk($disk)->get($product->image_path));
        $this->assertLessThanOrEqual(config('neva.uploads.max_edge.product'), $w);
    }

    /** Yedek temizliği adet sınırını uygulamalı ve en yeniyi korumalı. */
    public function test_backup_prune_keeps_the_newest_and_respects_the_count(): void
    {
        $dir = storage_path('app/test-backups');
        \Illuminate\Support\Facades\File::ensureDirectoryExists($dir);
        foreach (glob($dir.'/*.zip') ?: [] as $f) {
            unlink($f);
        }

        // 10 sahte arşiv, biri diğerinden eski.
        for ($i = 0; $i < 10; $i++) {
            $path = $dir.'/nevaqr-2026-01-'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT).'_000000.zip';
            file_put_contents($path, str_repeat('x', 1024));
            touch($path, time() - (10 - $i) * 86400);
        }

        $this->artisan('neva:yedek', [
            '--dizin' => $dir,
            '--adet' => 3,
            '--azami' => '0',
            '--tut' => '0',
            '--gorsel-yok' => true,
        ])->assertSuccessful();

        $left = glob($dir.'/*.zip') ?: [];

        // 10 sahte + 1 gerçek arşiv üretildi, adet sınırı 3.
        $this->assertCount(3, $left);

        // Kalanların en yenisi az önce üretilen gerçek yedek olmalı: sahte
        // dosyaların hepsi günler öncesine tarihlendi, bu ise şimdi oluştu.
        usort($left, fn ($a, $b) => filemtime($b) <=> filemtime($a));
        $this->assertGreaterThan(
            time() - 120,
            filemtime($left[0]),
            'en yeni arşiv az önce üretilen yedek değil — yanlış dosya silinmiş'
        );

        // Silinenler EN ESKİLER olmalı; kalan ikisi sahtelerin en yenileri.
        $this->assertFileExists($dir.'/nevaqr-2026-01-10_000000.zip');
        $this->assertFileDoesNotExist($dir.'/nevaqr-2026-01-01_000000.zip');

        array_map('unlink', glob($dir.'/*.zip') ?: []);
        @rmdir($dir);
    }
}

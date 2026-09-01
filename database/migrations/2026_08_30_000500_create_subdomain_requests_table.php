<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alt domain onay kuyruğu.
 * Kullanıcı panelde bir alt domain adı ("lumina") yazıp "Onaya Gönder" dediğinde
 * buraya "pending" bir kayıt düşer. Admin panelden onayladığında:
 *   - restaurants.subdomain doldurulur
 *   - restaurants.status = 'approved', published_at = now()
 *   - alt domain anında canlıya geçer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subdomain_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('requested_subdomain');
            $table->string('status')->default('pending'); // pending | approved | rejected

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subdomain_requests');
    }
};

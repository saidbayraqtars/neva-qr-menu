<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kayıt / üyelik talep kuyruğu.
 *
 * Ziyaretçi "Kayıt Ol" sihirbazını doldurur (işletme + iletişim + paket seçimi) →
 * buraya 'pending' bir talep düşer. HESAP YARATILMAZ, ŞİFRE ALINMAZ.
 * Admin ödemeyi işaretleyip "Onayla" dediğinde:
 *   - User (owner) + Restaurant (draft) + Subscription (active) yaratılır
 *   - güçlü bir GEÇİCİ şifre üretilir (temp_password), admin panelde görünür
 *   - users.must_change_password = true  → kullanıcı ilk girişte şifre değiştirmeye zorlanır
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_requests', function (Blueprint $table) {
            $table->id();

            $table->string('business_name');
            $table->string('name');                 // yetkili ad soyad
            $table->string('email');
            $table->string('phone')->nullable();

            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('table_count')->nullable();   // fiziksel QR paketi için
            $table->decimal('amount', 10, 2)->default(0);         // seçilen pakete göre hesaplanan tutar

            $table->string('status')->default('pending');          // pending | approved | rejected
            $table->string('payment_status')->default('unpaid');   // unpaid | paid

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();  // onayda oluşan hesap
            $table->string('temp_password')->nullable();           // admin'in kullanıcıya ileteceği geçici şifre (düz metin)

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_requests');
    }
};

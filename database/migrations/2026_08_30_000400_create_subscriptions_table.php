<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Basit abonelik modeli (MVP). İleride Laravel Cashier'a taşınabilir;
 * "provider" + "external_id" alanları bu geçişi kolaylaştırmak için var.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                     // "Başlangıç", "Profesyonel"
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('currency', 3)->default('TRY');
            $table->string('interval')->default('month'); // month | year
            $table->unsignedInteger('trial_days')->default(14);
            $table->unsignedInteger('max_restaurants')->default(1);
            $table->unsignedInteger('max_products')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();

            $table->string('status')->default('trialing'); // trialing | active | past_due | canceled | expired
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('currency', 3)->default('TRY');
            $table->string('interval')->default('month');

            $table->string('provider')->nullable();         // 'stripe' | 'iyzico' | 'manual'
            $table->string('external_id')->nullable()->index();

            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_starts_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};

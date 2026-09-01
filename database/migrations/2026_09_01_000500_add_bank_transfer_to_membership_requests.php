<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Havale/EFT ile ödeme akışı.
 *  reference_code : havale açıklamasına yazılacak tekil kod (NQR-XXXXXX)
 *  paid_at        : admin ödemeyi işaretlediği an
 *  payment_note   : dekont / banka notu
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_requests', function (Blueprint $table) {
            $table->string('reference_code', 24)->nullable()->after('amount');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
            $table->string('payment_note')->nullable()->after('paid_at');

            $table->unique('reference_code');
        });
    }

    public function down(): void
    {
        Schema::table('membership_requests', function (Blueprint $table) {
            $table->dropUnique(['reference_code']);
            $table->dropColumn(['reference_code', 'paid_at', 'payment_note']);
        });
    }
};

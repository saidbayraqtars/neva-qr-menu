<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Panel içi çift yönlü mesajlaşma (işletme sahibi <-> platform admini).
 * Bir kullanıcının birden çok konusu (conversation) olabilir.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('restaurant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject');
            $table->string('status', 20)->default('open');       // open | closed
            $table->string('priority', 20)->default('normal');   // low | normal | high
            $table->timestamp('last_message_at')->nullable();
            $table->string('last_sender_role', 10)->nullable();  // user | admin
            $table->unsignedInteger('unread_for_user')->default(0);
            $table->unsignedInteger('unread_for_admin')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'last_message_at']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_role', 10);                   // user | admin
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['conversation_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->id();
            $table->string('user_type')->nullable(); // Model type untuk user
            $table->unsignedBigInteger('user_id')->nullable(); // ID user yang melakukan action
            $table->string('event'); // created, updated, deleted, etc.
            $table->string('auditable_type'); // Model type yang diaudit
            $table->unsignedBigInteger('auditable_id'); // ID dari model yang diaudit
            $table->json('old_values')->nullable(); // Data lama sebelum perubahan
            $table->json('new_values')->nullable(); // Data baru setelah perubahan
            $table->string('url')->nullable(); // URL saat action dilakukan
            $table->string('ip_address')->nullable(); // IP address user
            $table->string('user_agent')->nullable(); // User agent browser
            $table->json('tags')->nullable(); // Tags untuk kategorisasi
            $table->timestamps();
            
            // Indexes untuk performance
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'user_type']);
            $table->index('event');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};

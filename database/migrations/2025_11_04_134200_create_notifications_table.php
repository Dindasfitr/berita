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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id('id_notification');
            $table->unsignedBigInteger('id_user');
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info'); // info, warning, success, error
            $table->boolean('is_read')->default(false);
            $table->json('data')->nullable(); // untuk menyimpan data tambahan
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('user')->onDelete('cascade');
            $table->index(['id_user', 'is_read']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

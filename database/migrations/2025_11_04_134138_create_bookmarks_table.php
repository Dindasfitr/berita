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
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id('id_bookmark');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_berita');
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('user')->onDelete('cascade');
            $table->foreign('id_berita')->references('id_berita')->on('berita')->onDelete('cascade');
            $table->unique(['id_user', 'id_berita']); // satu user tidak bisa bookmark berita yang sama 2x
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};

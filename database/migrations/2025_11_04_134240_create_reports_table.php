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
        Schema::create('reports', function (Blueprint $table) {
            $table->id('id_report');
            $table->unsignedBigInteger('id_user'); // pelapor
            $table->unsignedBigInteger('id_berita'); // berita yang dilaporkan
            $table->enum('reason', ['spam', 'konten_tidak_pantast', 'hoax', 'pelanggaran_hak_cipta', 'lainnya']);
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'resolved'])->default('pending');
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('user')->onDelete('cascade');
            $table->foreign('id_berita')->references('id_berita')->on('berita')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

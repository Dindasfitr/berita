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
        Schema::create('tidak_disukai', function (Blueprint $table) {
            $table->bigIncrements('id_tidaksuka');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_berita');
            $table->boolean('tidak_suka')->nullable(); // default null
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tidak_disukai');
    }
};

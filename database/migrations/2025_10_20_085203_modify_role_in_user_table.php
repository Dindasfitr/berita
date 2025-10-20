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
        Schema::table('user', function (Blueprint $table) {
            // Tambah kolom role kalau belum ada
            if (!Schema::hasColumn('user', 'role')) {
                $table->enum('role', ['admin', 'penulis', 'pembaca'])->after('password');
            } else {
                // Kalau kolom sudah ada, ubah tipe enum sesuai kebutuhan
                $table->enum('role', ['admin', 'penulis', 'pembaca'])->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
            if (Schema::hasColumn('user', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};

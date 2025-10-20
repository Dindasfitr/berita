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
            if (!Schema::hasColumn('user', 'membership')) {
                $table->enum('membership', ['guest', 'free', 'premium'])
                      ->after('role')
                      ->default('free');
            }
        });

        Schema::table('berita', function (Blueprint $table) {
            if (!Schema::hasColumn('berita', 'is_premium')) {
                $table->boolean('is_premium')
                      ->default(false)
                      ->after('tgl_terbit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('user', function (Blueprint $table) {
            if (Schema::hasColumn('user', 'membership')) {
                $table->dropColumn('membership');
            }
        });

        Schema::table('berita', function (Blueprint $table) {
            if (Schema::hasColumn('berita', 'is_premium')) {
                $table->dropColumn('is_premium');
            }
        });
    }
};

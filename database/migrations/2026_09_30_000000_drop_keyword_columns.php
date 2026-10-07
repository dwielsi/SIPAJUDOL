<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Deteksi konten ilegal kini sepenuhnya memakai LLM, sehingga penghitung dan daftar keyword manual tidak dipakai lagi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scan_results', function (Blueprint $table) {
            $table->dropColumn('keyword_count');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('scanner_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('scan_results', function (Blueprint $table) {
            $table->unsignedInteger('keyword_count')->default(0)->after('threat_type');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->string('scanner_keywords')->nullable();
        });
    }
};

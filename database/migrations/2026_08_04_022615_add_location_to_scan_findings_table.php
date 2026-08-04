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
        Schema::table('scan_findings', function (Blueprint $table) {
            $table->string('page_url')->nullable()->after('scan_result_id');
            $table->string('location')->nullable()->after('evidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scan_findings', function (Blueprint $table) {
            $table->dropColumn(['page_url', 'location']);
        });
    }
};

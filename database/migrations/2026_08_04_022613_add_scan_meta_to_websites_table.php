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
        Schema::table('websites', function (Blueprint $table) {
            $table->timestamp('last_scanned_at')->nullable()->after('status');
            $table->unsignedInteger('response_time_ms')->nullable()->after('last_scanned_at');
            $table->boolean('ssl_valid')->nullable()->after('response_time_ms');
            $table->timestamp('ssl_expires_at')->nullable()->after('ssl_valid');
            $table->unsignedInteger('latest_risk_score')->default(0)->after('ssl_expires_at');
            $table->string('baseline_hash')->nullable()->after('latest_risk_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn([
                'last_scanned_at',
                'response_time_ms',
                'ssl_valid',
                'ssl_expires_at',
                'latest_risk_score',
                'baseline_hash',
            ]);
        });
    }
};

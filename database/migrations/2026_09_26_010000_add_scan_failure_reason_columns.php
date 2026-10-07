<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->text('scan_failure_reason')->nullable()->after('status');
        });

        Schema::table('scan_results', function (Blueprint $table) {
            $table->text('failure_reason')->nullable()->after('current_step');
        });
    }

    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropColumn('scan_failure_reason');
        });

        Schema::table('scan_results', function (Blueprint $table) {
            $table->dropColumn('failure_reason');
        });
    }
};

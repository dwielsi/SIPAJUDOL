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
        Schema::table('scan_results', function (Blueprint $table) {
            $table->string('scan_state')->default('queued')->after('status');
            $table->unsignedTinyInteger('progress_percent')->default(0)->after('scan_state');
            $table->string('current_step')->nullable()->after('progress_percent');
            $table->timestamp('started_at')->nullable()->after('current_step');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->unsignedInteger('redirect_count')->default(0)->after('infected_pages');
            $table->unsignedInteger('malware_count')->default(0)->after('redirect_count');
            $table->unsignedInteger('external_link_count')->default(0)->after('malware_count');
            $table->text('ai_summary')->nullable()->after('findings_summary');
            $table->text('ai_conclusion')->nullable()->after('ai_summary');
            $table->text('ai_recommendation')->nullable()->after('ai_conclusion');
            $table->string('ai_priority')->nullable()->after('ai_recommendation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('scan_results', function (Blueprint $table) {
            $table->dropColumn([
                'scan_state',
                'progress_percent',
                'current_step',
                'started_at',
                'completed_at',
                'redirect_count',
                'malware_count',
                'external_link_count',
                'ai_summary',
                'ai_conclusion',
                'ai_recommendation',
                'ai_priority',
            ]);
        });
    }
};

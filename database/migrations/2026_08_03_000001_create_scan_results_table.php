<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->date('scan_date');
            $table->string('status')->default('safe');
            $table->unsignedInteger('risk_score')->default(0);
            $table->string('threat_type')->nullable();
            $table->unsignedInteger('keyword_count')->default(0);
            $table->unsignedInteger('judol_link_count')->default(0);
            $table->unsignedInteger('infected_pages')->default(0);
            $table->string('screenshot_path')->nullable();
            $table->text('findings_summary')->nullable();
            $table->json('details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_results');
    }
};

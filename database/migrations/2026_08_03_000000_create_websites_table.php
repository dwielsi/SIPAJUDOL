<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('opd_name');
            $table->string('website_name');
            $table->string('domain')->unique();
            $table->string('subdomain')->nullable();
            $table->string('ip_server')->nullable();
            $table->string('hosting')->nullable();
            $table->string('cms')->nullable();
            $table->string('cms_version')->nullable();
            $table->string('server_location')->nullable();
            $table->string('admin_name')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('admin_phone')->nullable();
            $table->string('status')->default('safe');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};

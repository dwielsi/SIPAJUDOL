<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * Database keyword manual diganti dengan screening konten ilegal berbasis LLM.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('keywords');

        $permissionsTable = config('permission.table_names.permissions', 'permissions');

        if (Schema::hasTable($permissionsTable)) {
            DB::table($permissionsTable)->where('name', 'like', 'keywords.%')->delete();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword')->unique();
            $table->string('category')->default('judol');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }
};

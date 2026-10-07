<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Akun di aplikasi ini login memakai username, bukan email, sehingga
     * tidak semua akun (terutama yang dibuat manual oleh admin) langsung
     * memiliki email. Kolom email diubah menjadi nullable supaya akun tanpa
     * email tersimpan sebagai benar-benar kosong (NULL), bukan diisi
     * karakter spasi sebagai akal-akalan supaya lolos constraint NOT NULL.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        // Bersihkan data lama yang sebelumnya diisi spasi kosong sebagai
        // penanda "belum ada email" agar konsisten menjadi NULL.
        DB::table('users')->where('email', ' ')->orWhere('email', '')->update(['email' => null]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });
    }
};

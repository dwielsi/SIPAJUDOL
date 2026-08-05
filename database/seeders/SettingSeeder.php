<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::firstOrCreate([], [
            'instansi_name' => 'Dinas Komunikasi dan Informatika Kabupaten Kubu Raya',
            'address' => 'Jl. Adisucipto, Sungai Raya, Kabupaten Kubu Raya, Kalimantan Barat',
            'head_name' => 'H. Ahmad Fauzi, S.Kom., M.T.',
            'nip' => '19800101 200501 1 001',
        ]);
    }
}
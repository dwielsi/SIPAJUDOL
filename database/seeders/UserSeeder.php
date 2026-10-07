<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $kabid = User::factory()->create([
            'name' => 'Kepala Bidang',
            'username' => 'kabid',
            'email' => null,
            'password' => 'password',
        ]);
        $kabid->assignRole(RoleEnum::Kabid->value);
    }
}
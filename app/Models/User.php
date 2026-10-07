<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RoleEnum;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'username', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Apakah akun admin (Kabid) sudah ada di sistem. Dipakai untuk membatasi
     * halaman pendaftaran akun hanya untuk membuat admin pertama kali saat
     * instalasi awal — setelah admin pertama ada, pendaftaran ditutup.
     *
     * Memakai whereHas (bukan scope role() bawaan Spatie) karena scope
     * tersebut melempar RoleDoesNotExist apabila role "kabid" itu sendiri
     * belum pernah dibuat, misalnya pada instalasi baru yang migrasinya
     * sudah jalan tapi seeder role & permission belum sempat dijalankan.
     */
    public static function adminAccountExists(): bool
    {
        return static::whereHas('roles', function ($query) {
            $query->where('name', RoleEnum::Kabid->value)->where('guard_name', 'web');
        })->exists();
    }
}

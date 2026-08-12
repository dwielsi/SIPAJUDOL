<?php

namespace App\Enums;

enum RoleEnum: string
{
    case Kabid = 'kabid';

    public function label(): string
    {
        return match ($this) {
            self::Kabid => 'Kepala Bidang',
        };
    }
}

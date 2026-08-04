<?php

namespace App\Enums;

enum RoleEnum: string
{
    case Kabid = 'kabid';
    case Operator = 'operator';

    public function label(): string
    {
        return match ($this) {
            self::Kabid => 'Kepala Bidang',
            self::Operator => 'Operator',
        };
    }
}

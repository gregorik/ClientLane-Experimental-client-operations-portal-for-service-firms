<?php

namespace App\Enums;

enum UserRole: string
{
    case Staff = 'staff';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Staff => 'Staff',
            self::Client => 'Client',
        };
    }
}

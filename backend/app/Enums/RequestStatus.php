<?php

namespace App\Enums;

enum RequestStatus: string
{
    case New = 'new';
    case WaitingOnStaff = 'waiting_on_staff';
    case WaitingOnClient = 'waiting_on_client';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::WaitingOnStaff => 'Waiting on staff',
            self::WaitingOnClient => 'Waiting on client',
            self::InProgress => 'In progress',
            self::Completed => 'Completed',
            self::Archived => 'Archived',
        };
    }
}

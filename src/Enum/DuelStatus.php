<?php

namespace App\Enum;

enum DuelStatus: string
{
    case Pending = 'pending';
    case Live = 'live';
    case Reveal = 'reveal';
    case Done = 'done';
}

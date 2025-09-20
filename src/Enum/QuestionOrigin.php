<?php

namespace App\Enum;

enum QuestionOrigin: string
{
    case Generator = 'generator';
    case Manual = 'manual';
}

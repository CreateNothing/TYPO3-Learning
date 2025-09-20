<?php

namespace App\Enum;

enum QuestionType: string
{
    case MultipleChoice = 'mcq';
    case ShortAnswer = 'short';
    case Code = 'code';
}

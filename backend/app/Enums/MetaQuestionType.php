<?php

namespace App\Enums;

enum MetaQuestionType: int
{
    case INPUT = 1;
    case TEXTAREA = 2;
    case SELECT = 3;
    case CHECKBOX = 4;
}

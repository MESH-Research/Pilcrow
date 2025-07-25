<?php
declare(strict_types=1);

namespace App\Enums;

enum MetaPromptType: int
{
    case INPUT = 1;
    case TEXTAREA = 2;
    case SELECT = 3;
    case CHECKBOX = 4;
}

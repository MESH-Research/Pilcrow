<?php
//phpcs:ignoreFile
declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class SubmissionFileStatusType extends Enum
{
    const Pending = 0;
    const Processing = 1;
    const Success = 2;
    const Failure = 3;
    const Cancelled = 4;
}

<?php
//phpcs:ignoreFile
declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static Pending()
 * @method static static Processing()
 * @method static static Success()
 * @method static static Failure()
 * @method static static Cancelled()
 */
final class SubmissionFileImportStatus extends Enum
{
    const Pending = 0;
    const Processing = 1;
    const Success = 2;
    const Failure = 3;
    const Cancelled = 4;
}

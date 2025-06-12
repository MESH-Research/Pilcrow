<?php
//phpcs:ignoreFile
declare(strict_types=1);

namespace App\Enums;

/**
 * @method static static Pending()
 * @method static static Processing()
 * @method static static Success()
 * @method static static Failure()
 * @method static static Cancelled()
 */
enum SubmissionFileImportStatus: int
{
    case Pending = 0;
    case Processing = 1;
    case Success = 2;
    case Failure = 3;
    case Cancelled = 4;
}

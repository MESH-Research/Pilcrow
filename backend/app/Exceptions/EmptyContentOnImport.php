<?php
declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Exception indicates that Pandoc returned no content when attempting to import a file.
 */
class EmptyContentOnImport extends Exception
{
    //
}

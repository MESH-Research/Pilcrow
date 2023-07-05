<?php
declare(strict_types=1);

namespace App\Exceptions;

use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;

class InvalidCredentials extends \Exception implements ClientAware, ProvidesExtensions
{
    /**
     * Returns true when exception message is safe to be displayed to a client.
     *
     * @api
     * @return bool
     */
    public function isClientSafe(): bool
    {
        return true;
    }

    public function getExtensions(): ?array
    {
      return   [
        'code' => 'INVALID_CREDENTIALS',
        'category' => 'authentication'
      ];
    }
}

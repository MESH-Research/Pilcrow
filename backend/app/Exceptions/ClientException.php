<?php
declare(strict_types=1);

namespace App\Exceptions;

use GraphQL\Error\ClientAware;
use GraphQL\Error\ProvidesExtensions;

class ClientException extends \Exception implements ClientAware, ProvidesExtensions
{
    /**
     * Construct a client exception
     *
     * @param string $message
     * @param string $category
     * @param string $clientCode
     */
    public function __construct(
        string $message,
        protected string $category = 'unknown',
        protected string $clientCode = 'UNKNOWN'
    ) {
        parent::__construct($message);
    }

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

    /**
     * Return extensions for error messsage.
     *
     * @return array|null
     */
    public function getExtensions(): ?array
    {
        return [
        'code' => $this->clientCode,
        'category' => $this->category,
        ];
    }
}

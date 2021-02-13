<?php

namespace App\Exceptions;

use Exception;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

class ClientException extends Exception implements RendersErrorsExtensions
{
    public $clientCode;
    public $category;

    public function __construct(string $message, string $category = null, string $clientCode)
    {
        $this->category = $category ?? 'unknown';
        $this->clientCode = $clientCode ?? 'UNKNOWN';
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
     * Returns string describing a category of the error.
     *
     * Value "graphql" is reserved for errors produced by query parsing or validation, do not use it.
     *
     * @api
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

     /**
     * Return the content that is put in the "extensions" part
     * of the returned error.
     *
     * @return array
     */
     public function extensionsContent(): array
     {
         return [
             'code' => strtoupper($this->clientCode)
         ];
     }
}
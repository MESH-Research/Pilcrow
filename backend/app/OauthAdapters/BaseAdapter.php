<?php
declare(strict_types=1);

namespace App\OauthAdapters;

use Exception;
use Laravel\Socialite\Contracts\Provider;

abstract class BaseAdapter implements OauthAdapterContract
{
    /**
     * @return \Laravel\Socialite\Contracts\Provider
     */
    abstract public static function resolveDriver(): Provider;

    /**
     * @return static
     */
    public static function throwIfDisabled(): static
    {
        if (!static::isEnabled()) {
            throw new Exception('Login provider is not configured.');
        }

        return new static();
    }

    /**
     * @return \Laravel\Socialite\Contracts\Provider
     */
    public static function getDriver(): Provider
    {
        return static::throwIfDisabled()->resolveDriver();
    }
}

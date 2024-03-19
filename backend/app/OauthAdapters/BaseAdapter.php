<?php
declare(strict_types=1);

namespace App\OauthAdapters;

abstract class BaseAdapter implements OauthAdapterContract
{
    /**
     * @return \Laravel\Socialite\Contracts\Provider
     */
    abstract public static function resolveDriver(): \Laravel\Socialite\Contracts\Provider;

    /**
     * @return static
     */
    public static function throwIfDisabled(): static
    {
        if (!static::isEnabled()) {
            throw new \Exception('Login provider is not configured.');
        }

        return new static();
    }

    /**
     * @return \Laravel\Socialite\Contracts\Provider
     */
    public static function getDriver(): \Laravel\Socialite\Contracts\Provider
    {
        return static::throwIfDisabled()->resolveDriver();
    }
}

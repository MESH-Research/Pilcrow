<?php
declare(strict_types=1);

namespace App\OauthAdapters;

use Laravel\Socialite\Facades\Socialite;

abstract class BaseAdapter implements OauthAdapterContract {
  abstract public static function resolveDriver(): \Laravel\Socialite\Contracts\Provider;

  public static function throwIfDisabled(): static
  {
    if (!static::isEnabled()) {
      throw new \Exception('Login provider is not configured.');
    }

    return new static();
  }

  public static function getDriver(): \Laravel\Socialite\Contracts\Provider
  {
    return static::throwIfDisabled()->resolveDriver();  }

}
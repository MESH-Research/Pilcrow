<?php
declare(strict_types=1);

namespace App\OauthAdapters;

use Laravel\Socialite\Facades\Socialite;

class GoogleAdapter extends BaseAdapter
{
    /**
     * @return bool
     */
    public static function isEnabled(): bool
    {
        $client_id = config('services.google.client_id');
        $client_secret = config('services.google.client_secret');
        $redirect = config('services.google.redirect');

        return $client_id && $client_secret && $redirect;
    }

    /**
     * @return \Laravel\Socialite\Contracts\Provider
     */
    public static function resolveDriver(): \Laravel\Socialite\Contracts\Provider
    {
        return Socialite::driver('google');
    }

    /**
     * @return string
     */
    public static function getIcon(): string
    {
        return 'google';
    }

    /**
     * @return string
     */
    public static function getLabel(): string
    {
        return 'Google';
    }

    /**
     * @return string
     */
    public static function getLoginUrl(): string
    {
        return static::getDriver()->redirect()->getTargetUrl();
    }

    /**
     * @param string $token
     * @return \Laravel\Socialite\Contracts\User
     */
    public static function getUserFromToken(string $token): \Laravel\Socialite\Contracts\User
    {
        /**
         * @var \Laravel\Socialite\Two\GoogleProvider $driver
         */
        $driver = static::getDriver();

        $response = $driver->getAccessTokenResponse($token);

        return $driver->userFromToken($response['access_token']);
    }
}

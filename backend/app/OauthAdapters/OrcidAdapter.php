<?php
declare(strict_types=1);

namespace App\OauthAdapters;

use Laravel\Socialite\Facades\Socialite;

class OrcidAdapter extends BaseAdapter
{
    /**
     * @return \Laravel\Socialite\Contracts\Provider
     */
    public static function resolveDriver(): \Laravel\Socialite\Contracts\Provider
    {
        /**
         * @var \Laravel\Socialite\One\OrcidProvider $driver
         */
        $driver = Socialite::driver('orcid');

        return $driver->setScopes(['/authenticate']);
    }

    /**
     * @return bool
     */
    public static function isEnabled(): bool
    {
        $client_id = config('services.orcid.client_id');
        $client_secret = config('services.orcid.client_secret');
        $redirect = config('services.orcid.redirect');

        return $client_id && $client_secret && $redirect;
    }

    /**
     * @return string
     */
    public static function getLoginUrl(): string
    {
        return self::getDriver()->redirect()->getTargetUrl();
    }

    /**
     * @return string
     */
    public static function getIcon(): string
    {
        return 'orcid';
    }

    /**
     * @return string
     */
    public static function getLabel(): string
    {
        return 'ORCID';
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

        return $driver->userFromToken($response);
    }
}

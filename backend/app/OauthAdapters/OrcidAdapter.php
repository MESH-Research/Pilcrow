<?php
declare(strict_types=1);

namespace App\OauthAdapters;

use Laravel\Socialite\Facades\Socialite;

class OrcidAdapter extends BaseAdapter
{
    public static function resolveDriver(): \Laravel\Socialite\Contracts\Provider
    {
        /**
         * @var \Laravel\Socialite\One\OrcidProvider $driver
         */
        $driver =  Socialite::driver('orcid');

        return $driver->setScopes(['/authenticate']);
    }

    public static function isEnabled(): bool
    {
        $client_id = config('services.orcid.client_id');
        $client_secret = config('services.orcid.client_secret');
        $redirect = config('services.orcid.redirect');

        return $client_id && $client_secret && $redirect;
    }

    public static function getLoginUrl(): string
    {
        return self::getDriver()->redirect()->getTargetUrl();
    }

    public static function getIcon(): string
    {
        return 'orcid';
    }

    public static function getLabel(): string
    {
        return 'orcid';
    }

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

<?php
declare(strict_types=1);

namespace App\OauthAdapters;

use Laravel\Socialite\Contracts\User;

interface OauthAdapterContract
{
    /**
     * @return string
     */
    public static function getLoginUrl(): string;

    /**
     * @return static
     */
    public static function throwIfDisabled(): static;

    /**
     * @return bool
     */
    public static function isEnabled(): bool;

    /**
     * @return string
     */
    public static function getIcon(): string;

    /**
     * @return string
     */
    public static function getLabel(): string;

    /**
     * @param string $token
     * @return \Laravel\Socialite\Contracts\User
     */
    public static function getUserFromToken(string $token): User;
}

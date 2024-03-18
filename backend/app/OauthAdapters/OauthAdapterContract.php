<?php
declare(strict_types=1);

namespace App\OauthAdapters;

interface OauthAdapterContract {
   public static function getLoginUrl(): string;
   public static function throwIfDisabled(): self;
   public static function isEnabled(): bool;
   public static function getIcon(): string;
   public static function getLabel(): string;
   public static function getUserFromToken(string $token): \Laravel\Socialite\Contracts\User;
}
<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\ExternalIdentityProvider;
use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

final readonly class LoginOrcidCallback
{
    /**
     * @link https://laravel.com/docs/master/socialite#routing
     * @param null $_
     * @param array{} $args
     * @return array
     */
    public function __invoke(null $_, array $args)
    {
        try {
            /** @var \App\GraphQL\Mutations\AbstractProvider $driver */
            $driver = Socialite::driver('orcid');
            $response = $driver->getAccessTokenResponse($args['code']);
            $socialiteUser = $driver->userFromToken($response);
            $provider = ExternalIdentityProvider::where('provider_name', 'orcid')
                ->where('provider_id', $socialiteUser->getId())
                ->first();
            if ($provider) {
                return $this->handleMatchedProviderId($provider);
            } elseif (!empty($socialiteUser->getEmail())) {
                $user = User::where('email', $socialiteUser->getEmail())->first();
                if ($user) {
                    return $this->handleMatchedEmail($socialiteUser, $user);
                } else {
                    return $this->handleUnmatchedEmail($socialiteUser);
                }
            } else {
                return $this->handleNoEmailNoMatchedProvider($socialiteUser);
            }
        } catch (\Exception $e) {
            throw new Error($e->getMessage());
        }
    }

    /**
     * @param \Laravel\Socialite\Two\User $socialiteUser
     * @return array
     */
    private function handleNoEmailNoMatchedProvider($socialiteUser): array
    {
        $user = [
            'name' => trim($socialiteUser->getName()),
            'username' => trim(User::generateUniqueUsername($socialiteUser->getName())),
            'email' => null,
        ];
        $provider = [
            'name' => 'orcid',
            'id' => $socialiteUser->getId(),
        ];
        return [
            'status' => 'register',
            'user' => $user,
            'provider' => $provider,
        ];
    }

    /**
     * @param \Laravel\Socialite\Two\User $socialiteUser
     * @return array
     */
    private function handleUnmatchedEmail($socialiteUser): array
    {
        $user = [
            'name' => trim($socialiteUser->getName()),
            'username' => trim(User::generateUniqueUsername($socialiteUser->getEmail())),
            'email' => $socialiteUser->getEmail(),
        ];
        $provider = [
            'name' => 'orcid',
            'id' => $socialiteUser->getId(),
        ];
        return [
            'status' => 'register',
            'user' => $user,
            'provider' => $provider
        ];
    }

    /**
     * @param \Laravel\Socialite\Two\User $socialiteUser
     * @param \App\Models\User $user
     * @return array
     */
    private function handleMatchedEmail($socialiteUser, $user): array
    {
        ExternalIdentityProvider::create([
            'provider_name' => 'orcid',
            'provider_id' => $socialiteUser->getId(),
            'user_id' => $user->id,
        ]);
        Auth::login($user);
        return [
            'status' => 'auth',
            'user' => null,
            'provider' => null
        ];
    }

    /**
     * @param \App\Models\ExternalIdentityProvider $provider
     * @return array
     */
    private function handleMatchedProviderId(ExternalIdentityProvider $provider): array
    {
        $user = User::find($provider->user_id)->first();
        Auth::login($user);
        return [
            'status' => 'auth',
            'user' => null,
            'provider' => null
        ];
    }
}

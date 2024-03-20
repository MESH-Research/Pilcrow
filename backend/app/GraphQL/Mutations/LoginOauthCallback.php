<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\ExternalIdentityProvider;
use App\Models\User;
use Error;
use Illuminate\Support\Facades\Auth;

final readonly class LoginOauthCallback
{
    private string $provider_name;

    /**
     * @link https://laravel.com/docs/master/socialite#routing
     * @param null $_
     * @param array{} $args
     * @return array
     */
    public function __invoke(null $_, array $args)
    {
        try {
            $this->provider_name = $args['provider_name'];
            // print_r($args['provider_name'] . "\n");
            // print_r('---');

            $providers = config('app.external_oauth_providers');

            if (!array_key_exists($this->provider_name, $providers)) {
                throw new Error('Invalid provider.');
            }

            $adapter = $providers[$this->provider_name];
            $adapter::getDriver();

            $socialiteUser = $adapter::getUserFromToken($args['code']);

            $providerEntity = ExternalIdentityProvider::where('provider_name', $this->provider_name)
                ->where('provider_id', $socialiteUser->getId())
                ->first();

            if ($providerEntity) {
                return $this->handleMatchedProviderId($providerEntity);
            }

            if (!empty($socialiteUser->getEmail())) {
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
            'provider_name' => $this->provider_name,
            'provider_id' => $socialiteUser->getId(),
        ];

        return [
            'action' => 'register',
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
            'provider_name' => $this->provider_name,
            'provider_id' => $socialiteUser->getId(),
        ];

        return [
            'action' => 'register',
            'user' => $user,
            'provider' => $provider,
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
            'provider_name' => $this->provider_name,
            'provider_id' => $socialiteUser->getId(),
            'user_id' => $user->id,
        ]);
        $user = Auth::guard('web')->loginUsingId($user);

        return [
            'action' => 'auth',
            'user' => null,
            'provider' => null,
        ];
    }

    /**
     * @param \App\Models\ExternalIdentityProvider $provider
     * @return array
     */
    private function handleMatchedProviderId(ExternalIdentityProvider $provider): array
    {
        $user = $provider->user;
        Auth::guard('web')->login($user);

        return [
            'action' => 'auth',
            'user' => null,
            'provider' => null,
        ];
    }
}

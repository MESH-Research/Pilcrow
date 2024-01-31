<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\ExternalIdentityProvider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

final readonly class LoginOrcidCallback
{
    /**
     * @link https://laravel.com/docs/master/socialite#routing
     * @param null $_
     * @param array{} $args
     * @return \App\Models\User
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
                    $this->handleMatchedEmail($socialiteUser, $user);
                } else {
                    $this->handleUnmatchedEmail($socialiteUser);
                }
            } else {
                $this->handleNoEmailNoProviderId($socialiteUser);
            }
        } catch (\Exception) {
            return redirect('/login');
        }
    }

    /**
     * @param \Laravel\Socialite\Two\User $socialiteUser
     * @return \App\Models\User
     */
    private function handleNoEmailNoProviderId($socialiteUser)
    {
        $user = User::create([
            'username' => User::generateUniqueUsername($socialiteUser->getName()),
            'email' => null,
            'email_verified_at' => Carbon::now(),
        ]);

        return $this->authenticateUser($user);
    }

    /**
     * @param \Laravel\Socialite\Two\User $socialiteUser
     * @return \App\Models\User
     */
    private function handleUnmatchedEmail($socialiteUser)
    {
        $user = User::create([
            'username' => User::generateUniqueUsername($socialiteUser->getEmail()),
            'email' => $socialiteUser->getEmail(),
            'email_verified_at' => Carbon::now(),
        ]);
        ExternalIdentityProvider::create([
            'provider_name' => 'orcid',
            'provider_id' => $socialiteUser->getId(),
            'user_id' => $user->id,
        ]);

        return $this->authenticateUser($user);
    }

    /**
     * @param \Laravel\Socialite\Two\User $socialiteUser
     * @param \App\Models\User $user
     * @return \App\Models\User
     */
    private function handleMatchedEmail($socialiteUser, $user): User
    {
        ExternalIdentityProvider::create([
            'provider_name' => 'orcid',
            'provider_id' => $socialiteUser->getId(),
            'user_id' => $user->id,
        ]);

        return $this->authenticateUser($user);
    }

    /**
     * @param \App\Models\ExternalIdentityProvider $provider
     * @return \App\Models\User
     */
    private function handleMatchedProviderId(ExternalIdentityProvider $provider): User
    {
        $user = User::find($provider->user_id)->first();

        return $this->authenticateUser($user);
    }

    /**
     * @todo use supplied user in authentiction
     * @param \App\Models\User $user
     * @return \App\Models\User
     */
    private function authenticateUser($user): User
    {
        print_r($user);
        $guard = Auth::guard('web');
        $guardUser = $guard->user();

        return $guardUser;
    }
}

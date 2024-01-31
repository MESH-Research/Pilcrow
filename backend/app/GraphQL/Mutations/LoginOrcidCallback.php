<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\ExternalIdentityProvider;
use App\Models\User;
use Carbon\Carbon;
use Laravel\Socialite\Facades\Socialite;

final readonly class LoginOrcidCallback
{
    /**
     * @link https://laravel.com/docs/master/socialite#routing
     * @param null $_
     * @param array{} $args
     * @return void
     */
    public function __invoke(null $_, array $args)
    {
        try {
            /** @var \App\GraphQL\Mutations\AbstractProvider $driver */
            $driver = Socialite::driver('orcid');
            $response = $driver->getAccessTokenResponse($args['code']);
            $socialiteUser = $driver->userFromToken($response);

            $provider = ExternalIdentityProvider::where('provider', 'orcid')
                ->where('provider_id', $socialiteUser->getId())
                ->first();

            if ($provider) {

                $user = User::find($provider->user_id)->first();
                // auth user

            } elseif (!empty($socialiteUser->getEmail())) {
                // an email was supplied

                $user = User::where('email', $socialiteUser->getEmail())->first();

                if ($user) {
                    // matched user by email
                    ExternalIdentityProvider::create([
                        'provider_name' => 'orcid',
                        'provider_id' => $socialiteUser->getId(),
                        'user_id' => $user->id,
                    ]);

                } else {
                    $usernameValue = !empty($socialiteUser->getName())
                        ? $socialiteUser->getName()
                        : $socialiteUser->getEmail();
                    User::create([
                        'username' => User::generateUniqueUsername($usernameValue),
                        'email' => $socialiteUser->getEmail(),
                        'email_verified_at' => Carbon::now(),
                    ]);

                }
                ExternalIdentityProvider::create([
                    'provider_name' => 'orcid',
                    'provider_id' => $socialiteUser->getId(),
                    'user_id' => $user->id,
                ]);

                // auth user

            } else {
                $usernameValue = !empty($socialiteUser->getName())
                    ? $socialiteUser->getName()
                    : $socialiteUser->getId();
                User::create([
                    'username' => User::generateUniqueUsername($usernameValue),
                    'email' => null,
                    'email_verified_at' => Carbon::now(),
                ]);

                // auth user
            }

        } catch (\Exception) {
            return redirect('/login');
        }
    }
}

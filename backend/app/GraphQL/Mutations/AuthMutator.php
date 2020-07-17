<?php

namespace App\GraphQL\Mutations;

use App\User;
use Illuminate\Support\Facades\Hash;
use App\Rules\Username;

class AuthMutator
{
    /**
     * Return a value for the field.
     *
     * @param  null  $rootValue Usually contains the result returned from the parent field. In this case, it is always `null`.
     * @param  mixed[]  $args The arguments that were passed into the field.
     */
    public function create($rootValue, array $args)
    {
        $args['password'] = Hash::make($args['password']);

        $user = User::create($args);

        return $user;
    }

    public function login($rootValue, array $args)
    {
        $UsernameValidator = new Username;
        if ($UsernameValidator->passes('', $args['email'])) {
            $username = $args['email'];
            $user = User::where('username', $username)->first();
            if (!$user) {
                return null;
            }

            $args['email'] = $user->value('email'); 

        }
        
        if (!auth()->attempt(['email' => $args['email'], 'password' => $args['password']])) {
            return null;
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        return $accessToken;
    }

    public function logout($rootValue, array $args)
    {
        auth()->user()->token()->revoke();
        return 'success';
    }
    
}

<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class IntegrationTesting
{
    /**
     * Run the supplied artisan command.
     *
     * @param  null  $_
     * @param  array<string, mixed>  $args
     * @return string
     */
    public function artisan($_, array $args): string
    {
        Artisan::call($args['command'], $this->parseKeyValues($args['parameters']) ?? []);

        return Artisan::output();
    }

    /**
     * Run the factory create method on a model.
     *
     * @param null $_
     * @param array<string, mixed> $args
     * @return string
     */
    public function createFactory($_, array $args): Collection
    {
        return $this->callFactory('create', $args);
    }

    /**
     * Run the factory make method on a model.
     *
     * @param null $_
     * @param array<string, mixed> $args
     * @return string
     */
    public function makeFactory($_, array $args): Collection
    {
        return $this->callFactory('make', $args);
    }

    /**
     * Login a user without supplying credentials for testing.
     *
     * @param null $_
     * @param array<string, string> $args
     * @return \App\Models\User
     */
    public function forceLogin($_, array $args): User
    {
        $email = $args['email'];
        $user = User::where('email', $email)->firstOrFail();

        Auth::guard(config('sanctum.guard', 'web'))
            ->attempt(['email' => 'regularuser@ccrproject.dev', 'password' => 'regularPassword!@#']);

        return $user;
    }

    /**
     * Call the factory methods.
     *
     * @param string $mode Factory method to call.
     * @param array $args
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function callFactory(string $mode, array $args): Collection
    {
        $model = $args['model'];
        $times = $args['times'] ?? '1';

        $attributes = $this->parseKeyValues($args['attributes']) ?? [];

        $factory = app("App\\Models\\{$model}")->factory();

        $collection = $factory
            ->times(intval($times))
            ->$mode($attributes)
            ->each->setHidden([]);

        return $collection;
    }

    /**
     * Parse key value paired associative arrays into an associative array.
     *
     * @param array<string, string> $kvArray
     * @return array
     */
    protected function parseKeyValues(array $kvArray): array
    {
        $assArray = [];
        foreach ($kvArray as $item) {
            $assArray[$item['key']] = $item['value'];
        }

        return $assArray;
    }
}

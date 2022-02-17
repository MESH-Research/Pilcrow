<?php
declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    /**
     * Application site name
     *
     * @var string
     */
    public string $site_name;

    /**
     * Group name
     *
     * @return  string  name of group
     */
    public static function group(): string
    {
        return 'general';
    }

    /**
     * Mutation field resolver
     *
     * @see \GraphQL\Executor\Executor::defaultFieldResolver
     * @param mixed                $_
     * @param array<string, mixed> $args
     * @param mixed                $__
     * @param mixed                $___
     * @return mixed
     */
    public function mutate($_, array $args, $__, $___): array
    {
        foreach ($args as $name => $value) {
            $this->{$name} = $value;
        }
        $this->save();

        return $this->query(null, [], null, null);
    }

    /**
     * Query field resolver
     *
     * @see \GraphQL\Executor\Executor::defaultFieldResolver
     * @param mixed $_
     * @param array<string, mixed> $__
     * @param mixed $___
     * @param mixed $____
     * @return array
     */
    public function query($_, $__, $___, $____): array
    {
        $query = $this->toArray();

        return $query;
    }
}

<?php

declare(strict_types=1);

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgResolver;

final class UpdateUsersDirective extends BaseDirective implements ArgResolver
{
    /**
     * @param  mixed  $root  The result of the parent resolver.
     * @param  mixed|\Nuwave\Lighthouse\Execution\Arguments\ArgumentSet|array<\Nuwave\Lighthouse\Execution\Arguments\ArgumentSet>  $value  The slice of arguments that belongs to this nested resolver.
     *
     * @return mixed|void|null May return the modified $root
     */
    public static function definition(): string
    {
        return
            /** @lang GraphQL */
            <<<'GRAPHQL'
"""
Nested ArgResolver for updating associated users.
"""
directive @updateUsers(
  """
  The relation to update users for.
  """
  relation: String
) on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION | INPUT_OBJECT
GRAPHQL;
    }


    /**
     * @param  mixed  $root  The result of the parent resolver.
     * @param  mixed|\Nuwave\Lighthouse\Execution\Arguments\ArgumentSet|array<\Nuwave\Lighthouse\Execution\Arguments\ArgumentSet>  $value  The slice of arguments that belongs to this nested resolver.
     *
     * @return mixed|void|null May return the modified $root
     */
    public function __invoke(mixed $root, mixed $value): mixed
    {
        $relationName = $this->directiveHasArgument('relation') ? $this->directiveArgValue('relation') : $this->nodeName();
        $message = $value->has('message') ? $value->arguments['message']->value : "";
        if ($value->has('connect')) {
            foreach ($value->arguments['connect']->value as $user) {
                $root->attachUser($user, $relationName, $message);
            }
        }
        if ($value->has('disconnect')) {
            foreach ($value->arguments['disconnect']->value as $user) {
                $root->detachUser($user, $relationName);
            }
        }

        if ($value->has('invite')) {
            foreach ($value->arguments['invite']->value as $email) {
                $root->{$relationName}()->invite($email);
            }
        }

        return $root->{$relationName}();
    }
}

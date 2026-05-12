<?php
declare(strict_types=1);

namespace App\GraphQL\Directives;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\Access\Gate;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class RedactIfDeniedDirective extends BaseDirective implements FieldMiddleware
{
    /**
     * @param \Illuminate\Contracts\Auth\Access\Gate $gate Authorization gate used to evaluate the configured ability.
     */
    public function __construct(
        protected Gate $gate
    ) {
    }

    /**
     * Run the resolver only if the current user passes the given ability
     * against the parent model. When denied (or unauthenticated), return
     * null instead of throwing — sibling fields keep resolving.
     */
    public function handleField(FieldValue $fieldValue): void
    {
        $ability = $this->directiveArgValue('ability');

        $fieldValue->wrapResolver(fn(callable $resolver): Closure => function (
            $root,
            array $args,
            GraphQLContext $context,
            ResolveInfo $resolveInfo
        ) use (
            $ability,
            $resolver
        ) {
            $user = $context->user();

            if ($user === null) {
                return null;
            }

            if (! $this->gate->forUser($user)->allows($ability, $root)) {
                return null;
            }

            return $resolver($root, $args, $context, $resolveInfo);
        });
    }

    /**
     * SDL definition for the @redactIfDenied directive consumed by Lighthouse.
     */
    public static function definition(): string
    {
        return <<<'GRAPHQL'
"""
Resolve the field only when the current user passes the given Gate ability
against the parent model. When the check fails the field resolves to null
without raising an authorization error, allowing sibling fields to resolve.
"""
directive @redactIfDenied(
  """
  The Gate ability to check. The parent model is passed as the ability target.
  """
  ability: String!
) on FIELD_DEFINITION
GRAPHQL;
    }
}

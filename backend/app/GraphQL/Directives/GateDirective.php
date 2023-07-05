<?php
declare(strict_types=1);

namespace App\GraphQL\Directives;

use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Contracts\Auth\Access\Gate;
use Nuwave\Lighthouse\Exceptions\AuthorizationException;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class GateDirective extends BaseDirective implements FieldMiddleware
{
    /**
     * Directive constructor
     *
     * @param \Illuminate\Contracts\Auth\Access\Gate $gate Inject gate contract
     */
    public function __construct(
        protected Gate $gate
    ) {
    }

    /**
     * Wrap around the final field resolver.
     *
     * @param  \Nuwave\Lighthouse\Schema\Values\FieldValue  $fieldValue
     * @return void
     */
    public function handleField(FieldValue $fieldValue): void
    {
        $ability = $this->directiveArgValue('ability');

        $fieldValue->wrapResolver(fn (callable $resolver): \Closure =>
            function (
                $root,
                array $args,
                GraphQLContext $context,
                ResolveInfo $resolveInfo
            ) use (
                $ability,
                $resolver
            ) {
                $gate = $this->gate->forUser($context->user());

                $response = $gate->inspect($ability);

                if ($response->denied()) {
                    throw new AuthorizationException($response->message(), $response->code());
                }

                return $resolver($root, $args, $context, $resolveInfo);
            });
    }

    /**
     * Documentation string from directive
     *
     * @return string Doc string
     */
    public static function definition(): string
    {
        return <<<'GRAPHQL'
"""
Check a Laravel Gate to ensure the current user is authorized to access a field.
"""
directive @ability(
  """
  The ability to check permissions for.
  """
  ability: String!

) repeatable on FIELD_DEFINITION
"""
Any constant literal value: https://graphql.github.io/graphql-spec/draft/#sec-Input-Values
"""
scalar CanArgs
GRAPHQL;
    }
}

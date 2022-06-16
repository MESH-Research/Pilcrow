<?php
declare(strict_types=1);

namespace App\GraphQL\Directives;

use Closure;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Arr;
use Nuwave\Lighthouse\Auth\CanDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ArgPolicyDirective extends CanDirective implements FieldMiddleware, FieldManipulator
{
    /**
     * Field definition
     *
     * @return string
     */
    public static function definition(): string
    {
        //phpcs:disable
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Check a Laravel Policy to ensure the current user is authorized to access a field.
When `injectArgs` and `args` are used together, the client given
arguments will be passed before the static args.
"""
directive @argPolicy(
  """
  The input args to apply policies to in the form of '<input>:<ability>'
  """
  apply: [String!]!
  """
  Query for specific model instances to check the policy against, using arguments
  with directives that add constraints to the query builder, such as `@eq`.
  Mutually exclusive with `find`.
  """
  query: Boolean = false
  """
  Apply scopes to the underlying query.
  """
  scopes: [String!]
  """
  Specify the class name of the model to use.
  This is only needed when the default model detection does not work.
  """
  model: String
  """
  Pass along the client given input data as arguments to `Gate::check`.
  """
  injectArgs: Boolean = false
  """
  Statically defined arguments that are passed to `Gate::check`.
  You may pass arbitrary GraphQL literals,
  e.g.: [1, 2, 3] or { foo: "bar" }
  """
  args: CanArgs
  """
  If your policy checks against specific model instances, specify
  the name of the field argument that contains its primary key(s).
  You may pass the string in dot notation to use nested inputs.
  Mutually exclusive with `search`.
  """
  find: String
) repeatable on FIELD_DEFINITION
"""
Any constant literal value: https://graphql.github.io/graphql-spec/draft/#sec-Input-Values
"""
scalar CanArgs
GRAPHQL;
     //phpcs:enable
    }

    /**
     * Check nested input fields for policies
     *
     * @param \Nuwave\Lighthouse\Schema\Values\FieldValue $fieldValue
     * @param \Closure $next
     * @return \Nuwave\Lighthouse\Schema\Values\FieldValue
     */
    public function handleField(FieldValue $fieldValue, Closure $next): FieldValue
    {
        $previousResolver = $fieldValue->getResolver();
        $apply = $this->directiveArgValue('apply');

        $fieldValue->setResolver(
            function (
                $root,
                array $args,
                GraphQLContext $context,
                ResolveInfo $resolveInfo
            ) use (
                $previousResolver,
                $apply
            ) {
                $argCollection = collect($args);
                foreach ($apply as $applyField) {
                    [$fieldToCheck, $ability] = explode(':', $applyField);
                    $argValue = Arr::get($args, $fieldToCheck);
                    if ($argValue !== null) {
                        $gate = $this->gate->forUser($context->user());
                        $checkArguments = $this->buildCheckArguments($args);

                        foreach ($this->modelsToCheck($resolveInfo->argumentSet, $args) as $model) {
                            $this->authorize($gate, $ability, $model, $checkArguments);
                        }
                    }
                }

                return $previousResolver($root, $args, $context, $resolveInfo);
            }
        );

        return $next($fieldValue);
    }
}

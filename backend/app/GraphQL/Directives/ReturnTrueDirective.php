<?php

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Schema\Context as GraphQLContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Return true for a field.  This is primarily used when the subfields have validation rules and the 
 * root field only needs to return true if the validations passed.
 */
class ReturnTrueDirective extends BaseDirective implements FieldResolver
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'SDL'
"""
Simple empty resolver that returns true.  Useful on validation only queries.
"""
directive @returnTrue on FIELD_DEFINITION
SDL;
    }

    public function resolveField(FieldValue $fieldValue): FieldValue
    {
       return $fieldValue->setResolver(function ($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo){
           return true;
       });
    }

}

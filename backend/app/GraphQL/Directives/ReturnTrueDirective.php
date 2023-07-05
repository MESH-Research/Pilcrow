<?php
declare(strict_types=1);

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;

/**
 * Return true for a field.  This is primarily used when the subfields have validation rules and the
 * root field only needs to return true if the validations passed.
 */
class ReturnTrueDirective extends BaseDirective implements FieldResolver
{
    /**
     * Documentation for directive
     *
     * @return string
     */
    public static function definition(): string
    {
        //phpcs:disable
        return /** @lang GraphQL */ <<<'SDL'
"""
Simple empty resolver that returns true.  Useful on validation only queries.
"""
directive @returnTrue on FIELD_DEFINITION
SDL;
        //phpcs:enable
    }

    /**
     * Resolve fields to true.
     *
     * @param \Nuwave\Lighthouse\Schema\Values\FieldValue $_
     * @return callable
     */
    public function resolveField(FieldValue $_): callable
    {
        return function () {
            return true;
        };
    }
}

<?php
declare(strict_types=1);

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgSanitizerDirective;

final class WebsiteDirective extends BaseDirective implements ArgSanitizerDirective, ArgDirective
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
directive @website on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION
GRAPHQL;
    }

    /**
     * Sanitize a value for a supplied website.
     *
     * @param  string  $argumentValue
     */
    public function sanitize($argumentValue): string
    {
        return strip_tags($argumentValue);
    }
}

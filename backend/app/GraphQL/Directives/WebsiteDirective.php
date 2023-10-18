<?php
declare(strict_types=1);

namespace App\GraphQL\Directives;

use HTMLPurifier;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgSanitizerDirective;

final class WebsiteDirective extends BaseDirective implements ArgSanitizerDirective, ArgDirective
{
    /**
     * @return string
     */
    public static function definition(): string
    {
        //phpcs:disable
        return /** @lang GraphQL */ <<<'GRAPHQL'
directive @website on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION
GRAPHQL;
        //phpcs:enable
    }

    /**
     * Sanitize a value for a supplied website.
     *
     * @param mixed $argumentValue
     * @return null|string
     */
    public function sanitize($argumentValue): null|string
    {
        if (is_null($argumentValue)) {
            return $argumentValue;
        }
        // Remove spaces
        $no_spaces = preg_replace('/\s/', '', $argumentValue);
        // Remove non-breaking spaces
        $no_nbsps = preg_replace('~\x{00a0}~', '', $no_spaces);
        // Purify
        $purifier = new HTMLPurifier();

        return $purifier->purify($no_nbsps);
    }
}

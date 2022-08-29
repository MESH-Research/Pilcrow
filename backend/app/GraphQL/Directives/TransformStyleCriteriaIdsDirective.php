<?php
declare(strict_types=1);

namespace App\GraphQL\Directives;

use App\Models\StyleCriteria;
use Exception;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgTransformerDirective;

class TransformStyleCriteriaIdsDirective extends BaseDirective implements ArgTransformerDirective, ArgDirective
{
    /**
     * @return string
     */
    public static function definition(): string
    {
        //phpcs:disable
        return /* @lang GraphQL */ <<< 'GRAPHQL'
            """
            Transform an array of style criteria IDs into JSON-serialized arrays
            """
            directive @transformStyleCriteriaIds on ARGUMENT_DEFINITION
        GRAPHQL;
        //phpcs:enable
    }

    /**
     * @param string $id
     * @return \App\Models\StyleCriteria
     * @throws \Exception
     */
    public function transform($id): StyleCriteria
    {
        try {
            return StyleCriteria::where('id', $id)->firstOrFail();
        } catch (Exception $e) {
            throw new Exception('An invalid style criteria ID was supplied.');
        }
    }
}

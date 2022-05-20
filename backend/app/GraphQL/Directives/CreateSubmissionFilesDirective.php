<?php
declare(strict_types=1);

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\ArgResolver;

class CreateSubmissionFilesDirective extends BaseDirective implements ArgResolver
{
    /**
     * Undocumented function
     *
     * @return string
     */
    public static function definition(): string
    {
        //phpcs:disable
        return /* @lang GraphQL */ <<< 'GRAPHQL'
            """
            Process submission uploads
            """
            directive @createSubmissionFiles on ARGUMENT_DEFINITION
        GRAPHQL;
        //phpcs:enable
    }

    /**
     * Undocumented function
     *
     * @param \App\GraphQL\Directives\Submission $parent
     * @param [type] $argsList
     * @return array
     */
    public function __invoke($parent, $argsList): array
    {
        $submissionFile = $parent->files()->make();

        if ($argsList->has('create')) {
            $newFiles = $argsList->toArray()['create'];

            return array_map(
                function ($newFile) use ($submissionFile) {
                    $submissionFile['file_upload'] = $newFile->storePublicly('uploads');
                    $submissionFile->save();
                },
                $newFiles
            );
        }

        return [];
    }
}

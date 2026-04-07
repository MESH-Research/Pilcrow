<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations\IntegrationTesting;

use App\Models\Submission;
use App\Models\SubmissionContent;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class SeedSubmission
{
    /**
     * Create a test submission with the specified status and role assignments.
     *
     * The submission is created with change tracking active (via the
     * X-Test-Token header), so it will be rolled back automatically
     * after the test completes.
     *
     * @param mixed  $_  unused root value
     * @param array<string, mixed>  $args
     */
    public function __invoke($_, array $args): Submission
    {
        abort_unless(
            App::environment(['local', 'testing']),
            403,
            'Seed submission is not available in this environment.'
        );

        $status = $args['status'] ?? Submission::UNDER_REVIEW;
        $title = $args['title'] ?? 'Test Submission ' . uniqid();
        $publicationId = $args['publication_id'] ?? 1;
        $withContent = $args['with_content'] ?? true;

        $submitter = User::firstWhere('username', 'regularUser');

        // Authenticate as the submitter so CreatedUpdatedBy trait works
        Auth::login($submitter);
        $coordinator = User::firstWhere('username', 'reviewCoordinator');
        $reviewer = User::firstWhere('username', 'reviewer');

        $factory = Submission::factory()
            ->hasAttached($submitter, [], 'submitters')
            ->hasAttached($coordinator, [], 'reviewCoordinators')
            ->hasAttached($reviewer, [], 'reviewers');

        if ($withContent) {
            $factory = $factory->has(
                SubmissionContent::factory()->count(3),
                'contentHistory'
            );
        }

        $submission = $factory->create([
            'title' => $title,
            'publication_id' => $publicationId,
            'created_by' => $submitter->id,
            'updated_by' => $submitter->id,
            'status' => $status,
        ]);

        if ($withContent) {
            $submission->content()->associate($submission->contentHistory->last());
            $submission->save();
        }

        return $submission;
    }
}

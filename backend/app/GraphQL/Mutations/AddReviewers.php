<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Role;
use App\Models\Submission;
use App\Models\SubmissionInvitation;
use Illuminate\Support\Facades\DB;

/**
 * Bulk-add reviewers to a submission. Handles existing users
 * (`connect: [ID!]`) and to-be-invited emails (`invite_emails:
 * [String!]`) in a single round trip with one shared message.
 *
 * The previous flow ran one `updateSubmission(reviewers.connect)`
 * call plus N `inviteReviewer` calls — fine for a single name, but
 * the manage UI's reviewer picker fans out to many at once. Doing
 * each invitation as its own mutation also meant a failure halfway
 * through left the submission with a partial set of assignments
 * and no surfaced indication of which ones did go through. This
 * resolver wraps the whole batch in a transaction so callers get
 * all-or-nothing semantics.
 */
final class AddReviewers
{
    /**
     * @param null  $_
     * @param array{
     *   submission_id: int|string,
     *   connect?: array<int,int|string>,
     *   invite_emails?: array<int,string>,
     *   message?: string|null
     * }  $args
     * @return \App\Models\Submission
     */
    public function __invoke($_, array $args): Submission
    {
        $submission = Submission::where('id', $args['submission_id'])
            ->firstOrFail();

        $connect = $args['connect'] ?? [];
        $emails = $args['invite_emails'] ?? [];
        $message = $args['message'] ?? null;

        DB::transaction(function () use ($submission, $connect, $emails, $message) {
            if (! empty($connect)) {
                // syncWithoutDetaching keeps the call idempotent —
                // re-running with an already-attached user is a
                // no-op rather than an error. The reviewers relation
                // pins role_id via withPivotValue, so attach stamps
                // the reviewer role automatically.
                $submission->reviewers()->syncWithoutDetaching($connect);
            }

            foreach ($emails as $email) {
                $invite = SubmissionInvitation::create([
                    'submission_id' => $submission->id,
                    'role_id' => Role::REVIEWER_ROLE_ID,
                    'email' => $email,
                    'message' => $message,
                ]);
                $invite->inviteReviewer();
            }
        });

        return $submission->fresh();
    }
}

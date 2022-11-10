<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\SubmissionInvitation;

final class AcceptSubmissionInvite
{
    /**
     * Based on a supplied submission invitation token, check that a submission
     * invitation exists and then accept a submission invitation as the invited user
     *
     * @param  null  $_
     * @param  array{token: string}  $args
     * @return \App\Models\Submission
     */
    public function acceptInvite($_, $args)
    {
        $invite = SubmissionInvitation::where('token', $args['token'])->firstOrFail();

        return $invite->acceptInvite();
    }
}

<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Exceptions\ClientException;
use App\Models\SubmissionInvitation;
use Carbon\Carbon;

final class AcceptSubmissionInvite
{
    /**
     * Based on a supplied submission invitation uuid, token, and expiration, check that:
     * - a submission invitation for the supplied uuid exists
     * - the submission invitation is not expired
     * - the token is not invalid
     * and then accept the submission invitation as the invited user
     *
     * @param  null  $_
     * @param  array{token: string}  $args
     * @return \App\Models\Submission
     */
    public function acceptInvite($_, $args)
    {
        $invite = SubmissionInvitation::where('uuid', $args['uuid'])->firstOrFail();
        $now = Carbon::now()->timestamp;
        if ($now > $args['expires']) {
            throw new ClientException('Token Expired', 'invitationVerification', 'INVITATION_TOKEN_EXPIRED');
        }
        if (!$invite->verifyToken($args['token'], $args['expires'])) {
            throw new ClientException('Invalid Token', 'invitationVerification', 'INVITATION_TOKEN_INVALID');
        }

        return $invite->acceptInvite();
    }
}

<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SubmissionUser extends Pivot
{
    use HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * @param string $email
     * @param \App\Models\Submission $submission
     * @return \App\Models\User
     */
    public function createStagedReviewer(string $email, Submission $submission)
    {
        $user = User::createStagedUser($email);
        $submission->reviewers()->attach($user);

        return $user;
    }

    /**
     * @param string $email
     * @param \App\Models\Submission $submission
     * @return \App\Models\User
     */
    public function createStagedReviewCoordinator(string $email, Submission $submission)
    {
        $user = User::createStagedUser($email);
        $submission->reviewCoordinators()->attach($user);

        return $user;
    }
}

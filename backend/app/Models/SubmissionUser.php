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
     * @param String $email
     * @param Submission $submission
     * @return User
     */
    public function createStagedReviewer(String $email, Submission $submission)
    {
        $user = User::createStagedUser($email);
        $submission->reviewers()->associate($user);
        return $user;
    }

    /**
     * @param String $email
     * @param Submission $submission
     * @return User
     */
    public function createStagedReviewCoordinator(String $email, Submission $submission)
    {
        $user = User::createStagedUser($email);
        $submission->reviewCoordinators()->associate($user);
        return $user;
    }
}

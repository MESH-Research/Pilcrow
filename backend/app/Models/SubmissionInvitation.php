<?php
declare(strict_types=1);

namespace App\Models;

use App\Events\ReviewCoordinatorInvitationAccepted;
use App\Events\ReviewCoordinatorInvited;
use App\Events\ReviewerInvitationAccepted;
use App\Events\ReviewerInvited;
use App\Http\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SubmissionInvitation extends Model
{
    use HasFactory;
    use CreatedUpdatedBy;

    protected $fillable = [
        'email',
        'message',
        'accepted_at',
        'submission_id',
        'role_id',
        'uuid',
    ];

    /**
     * Set a default UUID upon creation
     *
     * @return void
     */
    protected static function booted()
    {
        static::created(function (SubmissionInvitation $invite) {
            $invite->uuid = Str::uuid()->toString();
            $invite->save();
        });
    }

    /**
     * Create a submission invitation token
     *
     * @return string
     * @param int|float|string $expires
     */
    private function makeToken(string $expires)
    {
        return hash_hmac(
            'sha256',
            "{$this->getKey()}#{$this->email}#{$expires}",
            config('app.key')
        );
    }

    /**
     * Return URL for invitation acceptance
     *
     * @return string
     */
    public function getInvitationAcceptanceUrl(): string
    {
        $expires = (string)Carbon::now()->addMinutes(config('auth.verification.expire', 60))->timestamp;
        $hash = $this->makeToken($expires);

        return url("accept-invite/{$this->uuid}/{$expires}/{$hash}");
    }

    /**
     * Verify a submission invitation token
     *
     * @param string $token
     * @param string $expires
     * @return bool
     */
    public function verifyToken(string $token, string $expires): bool
    {
        return hash_equals($this->makeToken($expires), $token);
    }

    /**
     * The submission that the invitation belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    /**
     * The role associated with the invitation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * User that created the submission invitation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User that most recently updated the submission invitation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Create a staged review coordinator to a submisison and dispatch the ReviewCoordinatorInvited event
     *
     * @return \App\Models\Submission
     */
    public function inviteReviewCoordinator(): Submission
    {
        $this->stageReviewCoordinator($this->email);
        ReviewCoordinatorInvited::dispatch($this);

        return $this->submission;
    }

    /**
     * Create a staged reviewer to a submisison and dispatch the ReviewerInvited event
     *
     * @return \App\Models\Submission
     */
    public function inviteReviewer(): Submission
    {
        $this->stageReviewer($this->email);
        ReviewerInvited::dispatch($this);

        return $this->submission;
    }

    /**
     * Create a staged user and attach them as a reviewer to this submisison
     *
     * @param string $email
     * @return \App\Models\User|void
     */
    private function stageReviewer(string $email)
    {
        if (!$email) {
            return;
        }
        $user = User::createStagedUser($email);
        $this->submission->reviewers()->attach($user);

        return $user;
    }

    /**
     * Create a staged user and attach them as a review coordinator to this submisison
     *
     * @param string $email
     * @return \App\Models\User|void
     */
    private function stageReviewCoordinator(string $email)
    {
        if (!$email) {
            return;
        }
        $user = User::createStagedUser($email);
        $this->submission->reviewCoordinators()->attach($user);

        return $user;
    }

    /**
     * Set the submission invitation as accepted and unstage the invited user
     *
     * @return \App\Models\Submission
     */
    public function acceptInvite(): Submission
    {
        if ($this->accepted_at != null) {
            return $this->submission;
        }
        $user = User::where('email', $this->email)->firstOrFail();
        $this->updated_by = Auth::user()
            ? Auth::user()->id
            : $user->id;
        $this->accepted_at = Carbon::now()->toDateTimeString();
        $this->save();
        $user->staged = null;
        $user->save();

        if ((string)$this->role_id === Role::REVIEWER_ROLE_ID) {
            ReviewerInvitationAccepted::dispatch($this);
        }
        if ((string)$this->role_id === Role::REVIEW_COORDINATOR_ROLE_ID) {
            ReviewCoordinatorInvitationAccepted::dispatch($this);
        }

        return $this->submission;
    }
}

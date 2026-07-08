<?php
declare(strict_types=1);

namespace App\Models;

use App\Auth\Abilities\AbilityExposure;
use App\Auth\Abilities\PublicationAbility;
use App\Auth\Abilities\SubmissionAbility;
use App\Auth\Roles\ScopedRole;
use App\Auth\ScopedAbilityResolver;
use App\Builders\SubmissionBuilder;
use App\Events\SubmissionStatusUpdated;
use App\Http\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class Submission extends Model implements Auditable
{
    use HasFactory;
    use CreatedUpdatedBy;
    use AuditableTrait;

    public const DRAFT = 0;
    public const INITIALLY_SUBMITTED = 1;
    public const RESUBMISSION_REQUESTED = 2;
    public const RESUBMITTED = 3;
    public const AWAITING_REVIEW = 4;
    public const REJECTED = 5;
    public const ACCEPTED_AS_FINAL = 6;
    public const EXPIRED = 7;
    public const UNDER_REVIEW = 8;
    public const AWAITING_DECISION = 9;
    public const REVISION_REQUESTED = 10;
    public const ARCHIVED = 11;
    public const DELETED = 12;

    /**
     * Whether the submission is still a draft (the author's to shape).
     *
     * @return bool
     */
    public function isDraft(): bool
    {
        return $this->status === self::DRAFT;
    }

    /**
     * Whether the submission accepts review activity — i.e. it is UNDER_REVIEW.
     * New comments may only be created while a submission is reviewable.
     *
     * @return bool
     */
    public function isReviewable(): bool
    {
        return $this->status === self::UNDER_REVIEW;
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::updated(function ($submission) {
            $changes = $submission->getChanges();
            SubmissionStatusUpdated::dispatchIf(array_key_exists('status', $changes), $submission);
        });
    }

    /**
     * Restrict the query to submissions in publications managed by the
     * authenticated user:
     *  - Application administrators manage all publications, so see all submissions.
     *  - Users holding a publication role that grants publication.view (publication
     *    administrator or editor) see submissions in those publications. The role
     *    set is derived from the ScopedRole matrix so it tracks authorization.
     *  - Anyone else sees nothing.
     *
     * Relies on the @guard directive to reject unauthenticated requests before
     * this scope runs, so Auth::user() is always populated here.
     */
    public function scopeManagedPublicationSubmissions(Builder $query): Builder
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->isApplicationAdministrator()) {
            return $query;
        }

        $slugs = ScopedRole::grantingSlugsFor(PublicationAbility::View, ScopedRole::PIVOT_PUBLICATION);

        return $query->whereIn(
            'publication_id',
            $user->publications()->wherePivotIn('role', $slugs)->pluck('publications.id')
        );
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'publication_id',
        'status',
        'status_change_comment',
        'content_id',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'status_name',
    ];

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \App\Builders\SubmissionBuilder
     */
    public function newEloquentBuilder($query): SubmissionBuilder
    {
        return new SubmissionBuilder($query);
    }

    /**
     * The publication that the submission belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

    /**
     * Get the submission assignments for this submission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function submissionAssignments(): HasMany
    {
        return $this->hasMany(SubmissionAssignment::class, 'submission_id');
    }

    /** Users with reviewer role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function reviewers(): BelongsToMany
    {
        return $this->users()
            ->withPivotValue('role', ScopedRole::Reviewer->toSlug())
            ->withPivotValue('role_id', ScopedRole::Reviewer->legacyId());
    }

    /**
     * Users with a review_coordinator role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function reviewCoordinators(): BelongsToMany
    {
        return $this->users()
            ->withPivotValue('role', ScopedRole::ReviewCoordinator->toSlug())
            ->withPivotValue('role_id', ScopedRole::ReviewCoordinator->legacyId());
    }

    /**
     * Users with submitter role
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function submitters(): BelongsToMany
    {
        return $this->users()
            ->withPivotValue('role', ScopedRole::Submitter->toSlug())
            ->withPivotValue('role_id', ScopedRole::Submitter->legacyId());
    }

    /**
     * Users that belong to the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->using(SubmissionAssignment::class)
            ->withPivot(['id', 'user_id', 'role', 'submission_id']);
    }

    /**
     * File uploads that belong to the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class, 'submission_id');
    }

    /**
     * Primary content that belongs to the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(SubmissionContent::class);
    }

    /**
     * Content history that belongs to the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contentHistory(): HasMany
    {
        return $this->hasMany(SubmissionContent::class);
    }

    /**
     * Inline comments that belong to the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inlineComments(): HasMany
    {
        return $this->hasMany(InlineComment::class)->whereNull('parent_id');
    }

    /**
     * Inline comments and their replies that belong to the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inlineCommentsWithReplies(): HasMany
    {
        return $this->hasMany(InlineComment::class);
    }

    /**
     * Overall comments that belong to the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function overallComments(): HasMany
    {
        return $this->hasMany(OverallComment::class)->whereNull('parent_id');
    }

    /**
     * Overall comments and their replies that belong to the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function overallCommentsWithReplies(): HasMany
    {
        return $this->hasMany(OverallComment::class);
    }

    /**
     * Distinct users who have authored comments (including replies)
     *
     * @param string|null $type  "INLINE", "OVERALL", or null for all
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\User>
     */
    public function getCommentParticipants(?string $type = null)
    {
        $authorIds = collect();

        if ($type !== 'OVERALL') {
            $authorIds = $authorIds->merge($this->inlineCommentsWithReplies()->pluck('created_by'));
        }

        if ($type !== 'INLINE') {
            $authorIds = $authorIds->merge($this->overallCommentsWithReplies()->pluck('created_by'));
        }

        return User::whereIn('id', $authorIds->unique())->get();
    }

    /**
     * Invitations that belong to the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(SubmissionInvitation::class, 'submission_id');
    }

    /**
     * User that created the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * User that most recently updated the submission
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Datetime the submission was submitted
     *
     * @return string|null
     */
    public function getSubmittedAt()
    {
        return $this->audits()
            ->where('event', 'updated')
            ->where('old_values', 'like', '%"status":0%')
            ->where('new_values', 'like', '%"status":1%')
            ->first()->created_at ?? null;
    }

    /**
     * @return string
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            0 => 'DRAFT',
            1 => 'INITIALLY_SUBMITTED',
            2 => 'RESUBMISSION_REQUESTED',
            3 => 'RESUBMITTED',
            4 => 'AWAITING_REVIEW',
            5 => 'REJECTED',
            6 => 'ACCEPTED_AS_FINAL',
            7 => 'EXPIRED',
            8 => 'UNDER_REVIEW',
            9 => 'AWAITING_DECISION',
            10 => 'REVISION_REQUESTED',
            11 => 'ARCHIVED',
            12 => 'DELETED',
        ];

        return $statuses[(int)$this->status];
    }

    /**
     * Get the logged in users assigned role slug for this submission
     *
     * @return string|null
     */
    public function getMyRole(): ?string
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        $first = $this->submissionAssignments->first(function (SubmissionAssignment $assignment) use ($user) {
            return $assignment->user_id === $user->id;
        });

        if (!$first) {
            return null;
        }

        return $first->role ?? null;
    }

    /**
     * Get the logged in users role slug taking into account parent roles granted to the user
     *
     * @deprecated Display-only UI hint, NOT authorization — surfaced as the
     *   GraphQL `effective_role` field for the client, and it lossily collapses
     *   any parent-publication role to review_coordinator. Slated for
     *   replacement by per-entity capability flags. For authorization use
     *   {@see \App\Auth\ScopedAbilityResolver} / `$user->can()`, never this.
     * @return string|null
     */
    public function getEffectiveRole(): ?string
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        $publicationRole = $this->publication->getEffectiveRole();

        if ($publicationRole !== null) {
            return ScopedRole::ReviewCoordinator->toSlug();
        }

        return $this->getMyRole();
    }

    /**
     * The authenticated viewer's GRANTED scoped abilities on this submission,
     * as the exposed names of the exposed {@see SubmissionAbility} cases the
     * viewer holds, e.g. ['view', 'review'].
     *
     * Resolved through {@see ScopedAbilityResolver} — the same engine the
     * policies use — so these client-facing values can never drift from real
     * authorization. The resolver evaluates each ability against THIS submission
     * (and inherits the parent publication's admin roles), so conditional grants
     * such as draft-only status changes are reflected correctly. Only
     * {@see \App\Auth\Abilities\Exposed} cases are evaluated (server-only cases
     * never reach the wire, and their checks are never spent on the UI).
     * Guests get an empty array.
     *
     * UI hints only: the server still enforces every mutation with @can.
     *
     * @return array<int, string>
     */
    public function abilities(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user === null) {
            return [];
        }
        $resolver = app(ScopedAbilityResolver::class);

        $granted = [];
        foreach (AbilityExposure::exposed(SubmissionAbility::class) as $exposedName => $exposure) {
            /** @var \App\Auth\Abilities\SubmissionAbility $ability */
            $ability = $exposure['case'];
            if ($resolver->allows($user, $ability, $this)) {
                $granted[] = $exposedName;
            }
        }

        return $granted;
    }
}

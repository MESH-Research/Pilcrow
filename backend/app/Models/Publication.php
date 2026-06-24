<?php
declare(strict_types=1);

namespace App\Models;

use App\Auth\Roles\ScopedRole;
use App\Builders\PublicationBuilder;
use App\Models\Casts\CleanAdminHtml;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Publication extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'home_page_content',
        'new_submission_content',
        'is_publicly_visible',
        'is_accepting_submissions',
    ];

    protected $rules = [
        'name' => 'max:256|unique:publications,name|required',
        'is_publicly_visible' => 'boolean',
        'is_accepting_submissions' => 'boolean',
    ];

    protected $casts = [
        'home_page_content' => CleanAdminHtml::class,
        'new_submission_content' => CleanAdminHtml::class,
    ];

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \App\Builders\PublicationBuilder
     */
    public function newEloquentBuilder($query): PublicationBuilder
    {
        return new PublicationBuilder($query);
    }

    /**
     * Mutator: Trim name attribute before persisting
     *
     * @param string $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = is_string($value) ? trim($value) : $value;
    }

    /**
     * Users that belong to a publication
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot(['id', 'user_id', 'role', 'publication_id']);
    }

    /**
     * Publication administrators relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function publicationAdmins(): BelongsToMany
    {
        return $this->users()
            ->withPivotValue('role', ScopedRole::PublicationAdmin->toSlug())
            ->withPivotValue('role_id', ScopedRole::PublicationAdmin->legacyId());
    }

    /**
     * Editors relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function editors(): BelongsToMany
    {
        return $this->users()
            ->withPivotValue('role', ScopedRole::Editor->toSlug())
            ->withPivotValue('role_id', ScopedRole::Editor->legacyId());
    }

    /**
     * Submissions that belong to a publication
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Style Criteria Relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function styleCriterias(): HasMany
    {
        return $this->hasMany(StyleCriteria::class);
    }

    /**
     * Return the currently logged in user's role slug on this publication.
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

        $first = $this->users->first(
            fn(User $u) => $u->pivot->user_id === $user->id
        );

        if (!$first) {
            return null;
        }

        return $first->pivot->role;
    }

    /**
     * Return the effective role slug of a user on this publication taking into
     * account parent roles they may have.
     *
     * @return string|null
     */
    public function getEffectiveRole(): ?string
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        if ($user->isApplicationAdministrator()) {
            return ScopedRole::PublicationAdmin->toSlug();
        }

        return $this->getMyRole();
    }
}

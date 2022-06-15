<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_publicly_visible',
    ];

    protected $rules = [
        'name' => 'max:256|unique:publications,name|required',
        'is_publicly_visible' => 'boolean',
    ];

    /**
     * Mutator: Trim name attribute before persisting
     *
     * @param string $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = trim($value);
    }

    /**
     * Scope only publically visible publications.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsPubliclyVisible($query)
    {
        return $query->where('is_publicly_visible', true);
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
            ->withPivot(['id', 'user_id', 'role_id', 'publication_id']);
    }

    /**
     * Publication administrators relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function publicationAdmins(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivotValue('role_id', Role::PUBLICATION_ADMINISTRATOR_ROLE_ID);
    }

    /**
     * Editors  relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function editors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivotValue('role_id', Role::EDITOR_ROLE_ID);
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
}

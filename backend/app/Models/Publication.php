<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Casts\CleanAdminHtml;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Publication extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'home_page_content',
        'new_submission_content',
    ];

    protected $rules = [
        'name' => 'max:256|unique:publications,name|required',
    ];

    protected $casts = [
        'home_page_content' => CleanAdminHtml::class,
        'new_submission_content' => CleanAdminHtml::class,
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

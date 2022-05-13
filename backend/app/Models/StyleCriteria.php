<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Casts\CleanAdminHtml;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StyleCriteria extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'publication_id',
        'name',
        'description',
        'icon',
    ];

    protected $rules = [
        'name' => 'required|max:20',
        'publication_id' => 'required|style_criteria_count',
        'description' => 'max:4096',
        'icon' => 'max:50',
    ];

    protected $casts = [
        'description' => CleanAdminHtml::class,
    ];

    /**
     * Define publication relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    /**
     * Prepare a style_criteria_count rule, adding a model identifier if required.
     *
     * @param  array  $_
     * @param  string $__
     * @return string
     */
    protected function prepareStyleCriteriaCountRule($_, $__)
    {
        if ($this->exists) {
            return 'style_criteria_count:' . $this->getKey();
        }

        return 'style_criteria_count';
    }
}

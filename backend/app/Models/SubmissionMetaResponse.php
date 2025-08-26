<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasUserAuditFields;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionMetaResponse extends Model
{
    use HasTimestamps;
    use HasUserAuditFields;

    protected $table = 'submission_meta_responses';

    protected $fillable = [
        'submission_id',
        'meta_form_id',
        'responses',
        'prompts',
    ];

    protected $casts = [
        'responses' => 'array',
        'prompts' => 'array',
    ];

    /**
     * Get the meta form associated with this response.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function metaForm(): BelongsTo
    {
        return $this->belongsTo(MetaForm::class, 'meta_form_id');
    }

    /**
     * Get the submission associated with this response.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }
}

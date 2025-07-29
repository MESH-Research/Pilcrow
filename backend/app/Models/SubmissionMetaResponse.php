<?php

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
        'meta_page_id',
        'responses',
        'prompts',
    ];

    protected $casts = [
        'responses' => 'array',
        'prompts' => 'array',
    ];


    public function metaPage(): BelongsTo
    {
        return $this->belongsTo(MetaPage::class, 'meta_page_id');
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }
}

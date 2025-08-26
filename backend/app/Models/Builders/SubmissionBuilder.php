<?php
declare(strict_types=1);

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;

class SubmissionBuilder extends Builder
{
    /**
     * Scope submissions to those that have a specific submitter.
     *
     * @param int $userId The user ID of the submitter.
     * @return self
     */
    public function whereSubmitter($userId): self
    {
        return $this->whereHas('submitters', fn($query) => $query->where('user_id', $userId));
    }
}

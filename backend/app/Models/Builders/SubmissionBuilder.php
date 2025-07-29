<?php

declare(strict_types=1);

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;

class SubmissionBuilder extends Builder
{
    public function whereSubmitter($userId): self
    {
        return $this->whereHas('submitters', fn($query) => $query->where('user_id', $userId));
    }
}

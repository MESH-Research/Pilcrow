<?php

namespace App\Builders;

use Illuminate\Database\Eloquent\Builder;

class PublicationBuilder extends Builder
{
    /**
     * Scope only publically visible publications.
     *
     * @return self
     */
    public function isPubliclyVisible()
    {
        return $this->where('is_publicly_visible', true);
    }

    /**
     * Scope only publications that are accepting submissions
     *
     * @return self
     */
    public function isAcceptingSubmissions()
    {
        return $this->where('is_accepting_submissions', true);
    }

    /**
     * Add a scope to filter publications by a search string.
     *
     * @param string $search
     * @return self
     */
    public function search(mixed $search): self
    {
        return $this->where('name', 'like', '%' . $search . '%');
    }
}

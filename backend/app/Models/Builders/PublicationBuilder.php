<?php
declare(strict_types=1);

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;

class PublicationBuilder extends Builder
{
    /**
     * Scope publications to those that have a specific editor.
     *
     * @param int $userId The user ID of the editor.
     * @return self
     */
    public function whereEditor($userId): self
    {
        return $this->whereHas('editors', fn($query) => $query->where('user_id', $userId));
    }

    /**
     * Scope publications to those that have a specific admin.
     *
     * @param int $userId The user ID of the admin.
     * @return self
     */
    public function whereAdmin($userId): self
    {
        return $this->whereHas('publicationAdmins', fn($query) => $query->where('user_id', $userId));
    }

    /**
     * Scope publications to those that have a specific meta page.
     *
     * @param int $metaPageId The ID of the meta page.
     * @return self
     */
    public function whereMetaPage($metaPageId): self
    {
        return $this->whereHas('metaPages', fn($query) => $query->where('id', $metaPageId));
    }

    /**
     * Scope only publications that are accepting submissions.
     *
     * @return self
     */
    public function isAcceptingSubmissions(): self
    {
        return $this->where('is_accepting_submissions', true);
    }

    /**
     * Scope only publically visible publications.
     *
     * @return self
     */
    public function isPubliclyVisible(): self
    {
        return $this->where('is_publicly_visible', true);
    }
}

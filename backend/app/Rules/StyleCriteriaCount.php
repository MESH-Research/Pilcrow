<?php
declare(strict_types=1);

namespace App\Rules;

use App\Models\StyleCriteria;

class StyleCriteriaCount
{
    //TODO: Fetch this from configuration somewhere.
    public $maxCriteriaCount = 6;

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $_
     * @param  mixed  $publicationId
     * @param  array $params
     * @return bool
     */
    public function checkCount($_, $publicationId, $params)
    {
        if (!empty($params)) {
            [$criteriaId] = $params;
        }

        if ($this->maxCriteriaCount == 0) {
            //There is no limit, go nuts.
            return true;
        }

        $count = StyleCriteria::where('publication_id', $publicationId)
            ->whereNot('id', $criteriaId ?? null)
            ->count();

        if ($count == $this->maxCriteriaCount) {
            return false;
        }

        return true;
    }
}

<?php

namespace App\GraphQL\Bindings;

use App\Models\StyleCriteria;
use Illuminate\Support\Collection;
use Nuwave\Lighthouse\Bind\BindDefinition;

final class CommentStyleCriteria
{

    public function __invoke(mixed $value, BindDefinition $definition): Collection
    {
        return StyleCriteria::find($value)?->map->attributesToArray();
    }
}

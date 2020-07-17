<?php

namespace App\GraphQL\Directives;

use Nuwave\Lighthouse\Schema\Directives\ValidationDirective;
use App\User;

class CreateUserValidationDirective extends ValidationDirective
{
    public function rules(): array {
        return User::createRules();
    }
}

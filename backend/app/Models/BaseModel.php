<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Nuwave\Lighthouse\Exceptions\ValidationException;
use Watson\Validating\Injectors\UniqueWithInjector;
use Watson\Validating\ValidatingTrait;

class BaseModel extends Model
{
    use ValidatingTrait;
    use UniqueWithInjector;

    protected $throwValidationExceptions = true;

    /**
     * Declare the rules object to allow opt in.
     */
    protected $rules = [];

    /**
     * Override throw validation exception method to throw a lighthouse validation exception
     *
     * @return void
     */
    public function throwValidationException()
    {
        $validator = $this->makeValidator($this->getRules());

        throw new ValidationException('Validation failure', $validator);
    }
}

<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\StyleCriteria;
use Tests\TestCase;

class StyleCriteriaTest extends TestCase
{
    public function testValidationFailures()
    {
        $styleCriteria = new StyleCriteria();

        $styleCriteria->name = str_repeat('a', 21);

        $this->assertTrue($styleCriteria->isInvalid());
    }

    public function testDescriptionValidationFailures()
    {
        $styleCriteria = new StyleCriteria();

        $styleCriteria->name = str_repeat('a', 4097);

        $this->assertTrue($styleCriteria->isInvalid());
    }

    public function testValidationSuccess()
    {
        $styleCriteria = new StyleCriteria();

        $styleCriteria->name = str_repeat('a', 20);

        $styleCriteria->description = str_repeat('a', 4096);

        $this->assertTrue($styleCriteria->isValid());
    }

    public function testNameRequired()
    {
        $styleCriteria = new StyleCriteria();

        $this->assertFalse($styleCriteria->isValid());

        $styleCriteria->name = 'test name';

        $this->assertTrue($styleCriteria->isValid());
    }
}

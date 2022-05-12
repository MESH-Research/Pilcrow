<?php
    declare(strict_types=1);

    namespace Tests\Feature;

    use App\Models\StyleCriteria;
    use Illuminate\Support\Str;
    use Tests\TestCase;

class StyleCriteriaTest extends TestCase
{
    protected function makeTestCriteria()
    {
        return StyleCriteria::factory()->makeOne(['publication_id' => Str::uuid()]);
    }

    public function testNameValidationFailures()
    {
        $styleCriteria = $this->makeTestCriteria();

        $styleCriteria->name = str_repeat('a', 21);

        $this->assertTrue($styleCriteria->isInvalid());
        $this->assertCount(1, $styleCriteria->getErrors()->get('name'));
    }

    public function testDescriptionValidationFailures()
    {
        $styleCriteria = $this->makeTestCriteria();

        $styleCriteria->description = str_repeat('a', 4097);

        $this->assertTrue($styleCriteria->isInvalid());
        $this->assertCount(1, $styleCriteria->getErrors()->get('description'));
    }

    public function testValidationSuccess()
    {
        $styleCriteria = $this->makeTestCriteria();

        $this->assertTrue($styleCriteria->isValid());
    }

    public function testNameRequired()
    {
        $styleCriteria = $this->makeTestCriteria();

        $styleCriteria->name = '';

        $this->assertFalse($styleCriteria->isValid());
        $this->assertCount(1, $styleCriteria->getErrors()->get('name'));

        $styleCriteria->name = 'test name';

        $this->assertTrue($styleCriteria->isValid());
    }

    public function testIconMax()
    {
        $styleCriteria = $this->makeTestCriteria();

        $styleCriteria->icon = str_repeat('a', 51);

        $this->assertTrue($styleCriteria->isInvalid());
        $this->assertCount(1, $styleCriteria->getErrors()->get('icon'));
    }

    /**
     * Test data for admin editable html fields
     *
     * @return array
     */
    public function adminFieldHtml()
    {
        return [
            ['<b>Bold</b><i>Italic</i><u>Underline</u>','<b>Bold</b><i>Italic</i><u>Underline</u>'],
            ['<DIV><BR />Some text</DIV>', '<div><br />Some text</div>'],
            ['<script src="somewhere"/>', ''],
            ['<ol><li>Item</li></ol>', '<ol><li>Item</li></ol>'],
            ['<ul><li>Item</li></ol>', '<ul><li>Item</li></ul>'],
            ['<p style="font-size: 1000000px;">GIANT TEXT</p>', '<p>GIANT TEXT</p>'],
        ];
    }

    /**
     * @dataProvider adminFieldHtml
     * @param string $testHtml
     * @param string $expected
     * @return void
     */
    public function testStyleCritieraDescriptionFieldIsPurified($testHtml, $expected)
    {
        $criteria = StyleCriteria::factory()->make([
        'description' => $testHtml,
        ]);

        $this->assertEquals($expected, $criteria->description);
    }
}

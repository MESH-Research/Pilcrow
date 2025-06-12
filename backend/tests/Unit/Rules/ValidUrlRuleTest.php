<?php

namespace Tests\Unit\Rules;

use App\Rules\ValidUrl;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ValidUrlRuleTest extends TestCase
{

    public static function passingUrls()
    {
        return [
            ['msu.edu'],
            ['msu.edu/'],
            ['www.msu.edu/'],
            ['http://msu'],
            ['http://msu.'],
            ['http://msu.e'],
            ['http://msu.ed'],
            ['http://msu.edu'],
            ['http://msu.edu/'],
            ['https://cal.msu.edu'],
            ['go-gle.co'],
            ['www.msu.edu'],
        ];
    }

    public static function invalidUrls()
    {
        return [
            ['msu'],
            ['msu.'],
            ['www'],
            ['www.'],
            ['www.msu'],
            ['www.msu.'],
            ['www.msu.e'],
            ['www.msu.ed'],
            ['http'],
            ['http:'],
            ['http:/'],
            ['http://'],
            ['console.log("hi")'],
            ["<script>alert('hi')</script>google.com/"],
            ["<script>alert('hi')</script>google.com/about"],
            ["<script>alert('hi')</script>google.com"],
            ["<script>alert('hi')</script>http://google.com"],
            ["<script>alert('hi')</script>http://google.com/"],
            ["<script>alert('hi')</script>http://google.com/about"],
            ["<script>alert('hi')</script>https://google.com"],
            ["<script>alert('hi')</script>https://google.com/"],
            ["<script>alert('hi')</script>https://google.com/about"],
            ["javascript:alert('hi')"],
            ['google.<script>alert("Test Alert")</script>'],
            ['eval()'],
            ['Function()'],
            ['setTimeout()'],
            ['setInterval()'],
            ['setImmediate()'],
            ['execCommand()'],
            ['execScript()'],
            ['msSetImmediate()'],
            ['range.createContextualFragment()'],
            ['crypto.generateCRMFRequest()'],
            [null],
            [''],
        ];
    }

    #[DataProvider('passingUrls')]
    public function testPassesForValidUrls($value): void
    {
        $rule = new ValidUrl();

        $validator = Validator::make(['url' => $value], ['url' => $rule]);

        $this->assertTrue(
            $validator->passes(),
            "The URL '{$value}' should pass the validation."
        );
    }

    #[DataProvider('invalidUrls')]
    public function testFailsForInvalidUrls($value): void
    {
        $rule = new ValidUrl();

        $validator = Validator::make(['url' => $value], ['url' => $rule]);

        $this->assertFalse(
            $validator->passes(),
            "The URL '{$value}' should not pass the validation."
        );
        $this->assertNotEmpty(
            $validator->errors()->get('url'),
            "The URL '{$value}' should have validation errors."
        );
    }
}

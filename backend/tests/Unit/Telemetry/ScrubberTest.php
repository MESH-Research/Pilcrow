<?php
declare(strict_types=1);

namespace Tests\Unit\Telemetry;

use App\Telemetry\Scrubber;
use PHPUnit\Framework\TestCase;

class ScrubberTest extends TestCase
{
    public function testRedactsSensitiveTopLevelKeys(): void
    {
        $result = Scrubber::scrub([
            'email' => 'user@example.com',
            'password' => 'hunter2',
            'token' => 'abc',
            'username' => 'alice',
        ]);

        $this->assertSame('[Filtered]', $result['email']);
        $this->assertSame('[Filtered]', $result['password']);
        $this->assertSame('[Filtered]', $result['token']);
        $this->assertSame('alice', $result['username']);
    }

    public function testRedactsNestedSensitiveKeys(): void
    {
        $result = Scrubber::scrub([
            'headers' => [
                'Authorization' => 'Bearer xyz',
                'X-XSRF-TOKEN' => 'csrf',
                'User-Agent' => 'pilcrow',
            ],
        ]);

        $this->assertSame('[Filtered]', $result['headers']['Authorization']);
        $this->assertSame('[Filtered]', $result['headers']['X-XSRF-TOKEN']);
        $this->assertSame('pilcrow', $result['headers']['User-Agent']);
    }

    public function testFiltersVariablesForSensitiveGraphqlOperations(): void
    {
        $result = Scrubber::scrub([
            'operationName' => 'UpdateSubmissionContent',
            'variables' => ['id' => 7, 'content' => 'unpublished research'],
        ]);

        $this->assertSame('[Filtered]', $result['variables']);
    }

    public function testKeepsVariablesForBenignOperations(): void
    {
        $result = Scrubber::scrub([
            'operationName' => 'CurrentUser',
            'variables' => ['id' => 7],
        ]);

        $this->assertSame(['id' => 7], $result['variables']);
    }

    public function testIsCaseInsensitive(): void
    {
        $result = Scrubber::scrub(['EMAIL' => 'u@x.com', 'Cookie' => 'sid=1']);

        $this->assertSame('[Filtered]', $result['EMAIL']);
        $this->assertSame('[Filtered]', $result['Cookie']);
    }
}

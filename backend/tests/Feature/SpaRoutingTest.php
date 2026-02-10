<?php
declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SpaRoutingTest extends TestCase
{
    public static function clientRouteProvider(): array
    {
        return [
            'root' => ['/'],
            'single segment' => ['/dashboard'],
            'nested route' => ['/publication/1/submissions'],
            'deep nested route' => ['/submission/123/details'],
            'account settings' => ['/account/settings'],
            'review page' => ['/submission/456/review'],
        ];
    }

    #[DataProvider('clientRouteProvider')]
    public function testClientRoutesReturnSpaShell(string $url): void
    {
        $response = $this->get($url);

        $response->assertStatus(200);
        $response->assertViewIs('index');
    }
}

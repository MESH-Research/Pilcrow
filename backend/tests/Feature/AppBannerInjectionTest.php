<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class AppBannerInjectionTest extends TestCase
{
    public function testBannerInjectedWhenConfigSet(): void
    {
        config([
            'app.banner' => 'Heads up',
            'app.banner_class' => 'bg-red-2 text-black',
            'app.banner_link' => 'https://example.com/notice',
        ]);

        $expected = 'window.__APP_BANNER = ' . json_encode([
            'text' => 'Heads up',
            'class' => 'bg-red-2 text-black',
            'link' => 'https://example.com/notice',
        ]);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee($expected, false);
    }

    public function testBannerNullWhenConfigUnset(): void
    {
        config([
            'app.banner' => null,
            'app.banner_class' => null,
            'app.banner_link' => null,
        ]);

        $expected = 'window.__APP_BANNER = ' . json_encode([
            'text' => null,
            'class' => null,
            'link' => null,
        ]);

        $this->get('/')
            ->assertStatus(200)
            ->assertSee($expected, false);
    }
}

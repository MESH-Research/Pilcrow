<?php

namespace App\Providers;

use App\Rules\StyleCriteriaCount;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;

class AppServiceProvider extends ServiceProvider
{
    private const DEV_SCRIPTS = ['/@vite/client', '/.quasar/dev-spa/client-entry.js'];

    /**
     * Render dev script tags and check for drift against the Vite dev server.
     */
    public static function renderViteDevScripts(): string
    {
        $scripts = collect(self::DEV_SCRIPTS)
            ->map(fn ($src) => '<script type="module" src="' . e($src) . '"></script>')
            ->implode("\n    ");

        $drift = Cache::get('vite_dev_script_drift');
        if ($drift === null) {
            $drift = self::detectDrift();
        }

        return $scripts . $drift;
    }

    private static function detectDrift(): string
    {
        $html = @file_get_contents('http://client:8080/', false, stream_context_create([
            'http' => [
                'timeout' => 3,
                'header' => 'Host: ' . parse_url(config('app.url'), PHP_URL_HOST),
            ],
        ]));

        if (!$html) {
            return '';
        }

        preg_match_all('/<script[^>]*type="module"[^>]*src="([^"]+)"[^>]*>/', $html, $matches);

        $warning = '';
        if ($matches[1] !== self::DEV_SCRIPTS) {
            $expected = json_encode(self::DEV_SCRIPTS, JSON_UNESCAPED_SLASHES);
            $actual = json_encode($matches[1], JSON_UNESCAPED_SLASHES);
            $warning = "\n    <script>console.error("
                . json_encode("Vite dev scripts have changed! Update DEV_SCRIPTS in AppServiceProvider.php\nExpected: {$expected}\nActual: {$actual}")
                . ")</script>";
        }

        Cache::put('vite_dev_script_drift', $warning, 60);

        return $warning;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Register style criteria count rule.
        Validator::extend('style_criteria_count', StyleCriteriaCount::class . '@checkCount', 'Style criteria limit reached for this publication.');
        //Force https for generated URLs
        URL::forceScheme('https');

        Blade::directive('cdn_url', function ($expression) {

            if (config('app.cdn_base')) {
                return "<?php echo config('app.cdn_base') . '/' . ($expression); ?>";
            }
            return "<?php echo '/' . ($expression); ?>";
        });

        Blade::directive('vite_dev_scripts', function () {
            return "<?php echo \App\Providers\AppServiceProvider::renderViteDevScripts(); ?>";
        });
    }
}

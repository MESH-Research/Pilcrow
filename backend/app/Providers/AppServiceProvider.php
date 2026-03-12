<?php

namespace App\Providers;

use App\Rules\StyleCriteriaCount;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
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
    }
}

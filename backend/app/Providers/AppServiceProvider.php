<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Nuwave\Lighthouse\Events\BuildSchemaString;
use Nuwave\Lighthouse\Schema\Source\SchemaStitcher;

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
        // Register graphql routes for integration testing
        if (App::environment(['local', 'testing'])) {
            app('events')->listen(
                BuildSchemaString::class,
                function(): string {
                    $stitcher = new SchemaStitcher(base_path('graphql/integration_testing.graphql'));
                    return $stitcher->getSchemaString();
                }
            );
        }

        //Force https for generated URLs
        URL::forceScheme('https');
    }
}

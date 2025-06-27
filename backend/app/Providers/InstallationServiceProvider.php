<?php

namespace App\Providers;

use Database\Seeders\PermissionSeeder;
use Illuminate\Database\Events\SchemaLoaded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class InstallationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Event::listen(SchemaLoaded::class, fn($event) => $this->addDefaultRolesWhenSchemaLoaded($event));
    }

    public function addDefaultRolesWhenSchemaLoaded(SchemaLoaded $event)
    {
        $seeder = new PermissionSeeder();
        $seeder->run();
    }
}

<?php

namespace App\Providers;

use Database\Seeders\AbacSeeder;
use Illuminate\Database\Events\SchemaLoaded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
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

    /**
     * Seed the ABAC role/ability registry once the schema is available.
     *
     * The Bouncer tables are created by a migration that runs after the schema
     * dump is loaded, so guard on their presence; fresh migrations seed via
     * DatabaseSeeder / the test bootstrap instead.
     *
     * @param \Illuminate\Database\Events\SchemaLoaded $event
     * @return void
     */
    public function addDefaultRolesWhenSchemaLoaded(SchemaLoaded $event)
    {
        if (Schema::hasTable('bouncer_roles')) {
            (new AbacSeeder())->run();
        }
    }
}

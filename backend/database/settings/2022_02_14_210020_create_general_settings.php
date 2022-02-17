<?php
declare(strict_types=1);

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateGeneralSettings extends SettingsMigration
{
    /**
     * Run the migration
     *
     * @return void
     */
    public function up(): void
    {
        $this->migrator->add('general.site_name', 'Collaborative Community Review');
    }
}

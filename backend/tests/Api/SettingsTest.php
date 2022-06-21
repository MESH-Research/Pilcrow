<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\Role;
use App\Models\User;
use App\Settings\GeneralSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class SettingsTest extends ApiTestCase
{
    use RefreshDatabase;

    public function testSettingsCanBeRetreived()
    {
        $response = $this->graphQL('
            query GetSiteName {
                generalSettings {
                    site_name
                }
            }
        ');

        $this->assertEquals('Collaborative Community Review', $response->json('data.generalSettings.site_name'));
    }

    public function testSettingsCanBeSet()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(Role::APPLICATION_ADMINISTRATOR);
        $this->actingAs($user);

        $this->graphQL('
            mutation SetSiteName($site_name: String) {
                saveGeneralSettings(settings: {
                    site_name: $site_name
                }) {
                    site_name
                }
            }
        ', ['site_name' => 'new title']);

        $this->assertEquals('new title', app(GeneralSettings::class)->site_name);

        $response = $this->graphQL('
            query GetSiteName {
                generalSettings {
                    site_name
                }
            }
        ');

        $this->assertEquals('new title', $response->json('data.generalSettings.site_name'));
    }

    public function testRequiresPermission()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->graphQL('
            mutation SetSiteName($site_name: String) {
                saveGeneralSettings(settings: {
                    site_name: $site_name
                }) {
                    site_name
                }
            }
        ', ['site_name' => 'new title']);

        $error = $response->json('errors.0.extensions.category');
        $this->assertEquals('authorization', $error);
    }
}

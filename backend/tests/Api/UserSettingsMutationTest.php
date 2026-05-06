<?php
declare(strict_types=1);

namespace Tests\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ApiTestCase;

class UserSettingsMutationTest extends ApiTestCase
{
    use RefreshDatabase;

    public function testUnauthenticatedCannotUpdatePreferences(): void
    {
        $response = $this->graphQL(
            'mutation { updateUserPreferences(input: { theme: DARK }) { id } }'
        );

        $response->assertJsonPath('data.updateUserPreferences', null);
    }

    public function testUnauthenticatedCannotDismissUiElement(): void
    {
        $response = $this->graphQL(
            'mutation { dismissUiElement(key: "any.key") { id } }'
        );

        $response->assertJsonPath('data.dismissUiElement', null);
    }

    public function testUnauthenticatedCannotSetFeatureOptIn(): void
    {
        $response = $this->graphQL(
            'mutation { setFeatureOptIn(feature: "any.feature", enabled: true) { id } }'
        );

        $response->assertJsonPath('data.setFeatureOptIn', null);
    }

    public function testUpdatePreferencesPersistsTypedFields(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation {
                updateUserPreferences(
                    input: { theme: DARK, a11y_color_patterns: true }
                ) {
                    preferences {
                        theme
                        a11y_color_patterns
                    }
                }
            }'
        );

        $response->assertJsonPath('data.updateUserPreferences.preferences.theme', 'DARK');
        $response->assertJsonPath(
            'data.updateUserPreferences.preferences.a11y_color_patterns',
            true
        );
        $this->assertSame(
            ['theme' => 'dark', 'a11y_color_patterns' => true],
            $user->fresh()->preferences
        );
    }

    public function testUpdatePreferencesIsPartial(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'preferences' => ['theme' => 'light', 'a11y_color_patterns' => true],
        ]);
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation {
                updateUserPreferences(input: { theme: DARK }) {
                    preferences {
                        theme
                        a11y_color_patterns
                    }
                }
            }'
        );

        // Theme updated, but the previously-stored a11y_color_patterns
        // value is untouched — partial patches must not clobber other
        // keys.
        $response->assertJsonPath('data.updateUserPreferences.preferences.theme', 'DARK');
        $response->assertJsonPath(
            'data.updateUserPreferences.preferences.a11y_color_patterns',
            true
        );
    }

    public function testDismissUiElementAddsKeyToList(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation {
                dismissUiElement(key: "manage_ui.opt_in_callout") {
                    dismissed_ui
                }
            }'
        );

        $response->assertJsonPath(
            'data.dismissUiElement.dismissed_ui.0',
            'manage_ui.opt_in_callout'
        );
    }

    public function testDismissUiElementRecordsTimestampForLaterCooldown(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->graphQL(
            'mutation { dismissUiElement(key: "any.key") { id } }'
        );

        $stored = $user->fresh()->dismissed_ui;
        $this->assertArrayHasKey('any.key', $stored);
        // Stored value is an ISO-8601 timestamp string, not just `true` —
        // future "show again after N days" needs the timestamp.
        $this->assertIsString($stored['any.key']);
        $this->assertNotEmpty($stored['any.key']);
    }

    public function testDismissUiElementIsIdempotentAndRefreshesTimestamp(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'dismissed_ui' => ['x' => '2020-01-01T00:00:00+00:00'],
        ]);
        $this->actingAs($user);

        $this->graphQL(
            'mutation { dismissUiElement(key: "x") { id } }'
        );

        $stored = $user->fresh()->dismissed_ui;
        // Still exactly one entry for the key, but the timestamp
        // advanced from the seeded 2020 value.
        $this->assertCount(1, $stored);
        $this->assertArrayHasKey('x', $stored);
        $this->assertNotSame('2020-01-01T00:00:00+00:00', $stored['x']);
    }

    public function testSetFeatureOptInTrueListsFeature(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation {
                setFeatureOptIn(feature: "manage_ui_v2", enabled: true) {
                    feature_opt_ins
                }
            }'
        );

        $response->assertJsonPath(
            'data.setFeatureOptIn.feature_opt_ins.0',
            'manage_ui_v2'
        );
    }

    public function testUnauthenticatedCannotResetDismissedUi(): void
    {
        $response = $this->graphQL(
            'mutation { resetDismissedUi { id } }'
        );

        $response->assertJsonPath('data.resetDismissedUi', null);
    }

    public function testResetDismissedUiClearsAllKeys(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'dismissed_ui' => [
                'manage_ui.opt_in_callout' => '2026-04-01T00:00:00+00:00',
                'team.flag_help' => '2026-04-15T12:00:00+00:00',
            ],
        ]);
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation { resetDismissedUi { dismissed_ui } }'
        );

        $response->assertJsonPath('data.resetDismissedUi.dismissed_ui', []);
        $this->assertSame([], $user->fresh()->dismissed_ui);
    }

    public function testSetFeatureOptInFalseRecordsOptOutButHidesFromList(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            // Pre-existing opt-in we're now reversing.
            'feature_opt_ins' => ['manage_ui_v2' => true],
        ]);
        $this->actingAs($user);

        $response = $this->graphQL(
            'mutation {
                setFeatureOptIn(feature: "manage_ui_v2", enabled: false) {
                    feature_opt_ins
                }
            }'
        );

        // GraphQL surface only lists *active* opt-ins, so the feature
        // disappears from the array...
        $response->assertJsonPath(
            'data.setFeatureOptIn.feature_opt_ins',
            []
        );
        // ...but the explicit `false` is still recorded so we can tell
        // "user opted out" apart from "user never decided".
        $this->assertSame(
            ['manage_ui_v2' => false],
            $user->fresh()->feature_opt_ins
        );
    }
}

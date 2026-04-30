<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class UserSettings
{
    /**
     * Patch a subset of the current user's preferences. Only fields
     * supplied on the input are written; absent keys keep whatever
     * value was previously stored.
     *
     * @param mixed $_
     * @param array<string, mixed> $args
     * @return \App\Models\User
     */
    public function updatePreferences($_, array $args): User
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $current = $user->preferences ?? [];
        $patch = array_filter(
            $args['input'] ?? [],
            fn($value) => $value !== null
        );

        $user->preferences = array_merge($current, $patch);
        $user->save();

        return $user->fresh();
    }

    /**
     * Record a UI element dismissal for the authenticated user. The
     * stored value is the dismissal timestamp so a future cooldown
     * window ("re-show after 30 days") can opt in without a schema
     * change. Re-dismissing the same key just refreshes the timestamp.
     *
     * @param mixed $_
     * @param array<string, mixed> $args
     * @return \App\Models\User
     */
    public function dismissUiElement($_, array $args): User
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $dismissed = $user->dismissed_ui ?? [];
        $dismissed[$args['key']] = Carbon::now()->toIso8601String();

        $user->dismissed_ui = $dismissed;
        $user->save();

        return $user->fresh();
    }

    /**
     * Toggle the authenticated user's opt-in for a feature flag.
     * Stores the boolean explicitly so we can distinguish "opted out"
     * (`false`) from "never asked" (key absent).
     *
     * @param mixed $_
     * @param array<string, mixed> $args
     * @return \App\Models\User
     */
    public function setFeatureOptIn($_, array $args): User
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $optIns = $user->feature_opt_ins ?? [];
        $optIns[$args['feature']] = (bool) $args['enabled'];

        $user->feature_opt_ins = $optIns;
        $user->save();

        return $user->fresh();
    }

    /**
     * Wipe the authenticated user's dismissed_ui map so every
     * dismissable callout they previously closed will show again
     * on their next visit.
     *
     * @param mixed $_
     * @param array<string, mixed> $args
     * @return \App\Models\User
     */
    public function resetDismissedUi($_, array $args): User
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $user->dismissed_ui = [];
        $user->save();

        return $user->fresh();
    }
}

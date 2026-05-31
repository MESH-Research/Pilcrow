<?php
declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\User;
use GraphQL\Error\Error;
use Illuminate\Support\Facades\Auth;

class FeatureOptIn
{
    /**
     * Toggle the authenticated user's opt-in for a beta feature.
     *
     * Opt-ins are stored as a flat array of enabled feature keys: opting
     * in adds the key, opting out removes it. Presence of the key IS the
     * grant. Opting *in* is gated: the feature key must be known and,
     * when it is beta-gated, the user must have beta access. Opting *out*
     * is always permitted so access can be revoked without leaving a
     * dangling opt-in.
     *
     * @param mixed $_
     * @param array<string, mixed> $args
     * @return \App\Models\User
     * @throws \GraphQL\Error\Error
     */
    public function setFeatureOptIn($_, array $args): User
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $feature = $args['feature'];
        $enabled = (bool)$args['enabled'];

        // Opting in is only valid for a currently-gated beta feature the
        // user can access. Opting out is always allowed so a stale
        // opt-in can be cleared (e.g. after beta access is revoked).
        if ($enabled) {
            if (!User::featureIsBetaGated($feature)) {
                throw new Error('Unknown beta feature.');
            }
            if (!$user->canAccessFeature($feature)) {
                throw new Error('Feature is not available to this user.');
            }
        }

        // Remove the key first (dedups / clears a prior opt-in), then
        // re-add only when opting in. Presence of the key is the grant.
        $optIns = array_values(array_diff($user->getActiveFeatureOptIns(), [$feature]));
        if ($enabled) {
            $optIns[] = $feature;
        }

        $user->feature_opt_ins = $optIns;
        $user->save();

        return $user->fresh();
    }

    /**
     * Grant or revoke a user's beta access. Authorization is enforced
     * by the `@can(ability: "manageBeta")` directive on the mutation;
     * this resolver only writes the flag.
     *
     * @param mixed $_
     * @param array<string, mixed> $args
     * @return \App\Models\User
     */
    public function setUserBetaAccess($_, array $args): User
    {
        /** @var \App\Models\User $user */
        $user = User::findOrFail($args['id']);

        $user->beta = (bool)$args['enabled'];
        $user->save();

        return $user->fresh();
    }
}

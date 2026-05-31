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
     * grant. The ONLY gate on opting in is that the feature key is known
     * (in the catalog); the `beta` flag is not checked here — beta is an
     * advertisement concern owned by the client, not a server security
     * boundary. Opting *out* is always permitted so a stale opt-in can
     * be cleared.
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

        // The only opt-in gate is a known feature key. Beta access is
        // not checked — it governs client advertisement, not server
        // authorization. Opting out is always allowed so a stale opt-in
        // can be cleared.
        if ($enabled && !User::featureExists($feature)) {
            throw new Error('Unknown feature.');
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

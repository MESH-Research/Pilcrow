<?php

/*
|--------------------------------------------------------------------------
| Beta Feature Catalog
|--------------------------------------------------------------------------
|
| The authoritative list of private-beta feature keys. A feature key
| listed here is gated: only users with the `beta` flag may opt into
| it (enforced server-side in setFeatureOptIn and User::hasFeatureEnabled).
|
| Lifecycle:
|   - To add a private beta feature, add its key here and reference it
|     from the gated code via $user->hasFeatureEnabled('<key>').
|   - When a feature "graduates" to general availability, REMOVE its key
|     from this list and remove the hasFeatureEnabled() gate around it.
|     It then becomes an always-on feature with no opt-in.
|
| Keys are technical identifiers persisted in users.feature_opt_ins;
| they must stay stable and should not track user-visible naming. The
| client owns all presentation (labels, descriptions, previews) for the
| beta-features page.
|
*/

return [
    /**
     * Private-beta feature keys. The client beta-features page lists
     * the matching entries; the server only needs the keys to validate
     * opt-ins and answer hasFeatureEnabled().
     */
    'beta' => [
        'labs_test',
    ],
];

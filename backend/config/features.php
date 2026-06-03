<?php

/*
|--------------------------------------------------------------------------
| Beta Feature Catalog
|--------------------------------------------------------------------------
|
| The authoritative list of known feature keys. This catalog is the
| sole server-side validity gate: setFeatureOptIn accepts an opt-in for
| any key listed here, from any authenticated user. The `beta` flag does
| NOT gate opting in — beta is an advertisement concern owned by the
| client (which decides what to show), not a server security boundary.
|
| Lifecycle:
|   - To add a feature, add its key here and reference it from the gated
|     code via $user->hasFeatureEnabled('<key>').
|   - When a feature "graduates" to general availability, REMOVE its key
|     from this list and remove the hasFeatureEnabled() gate around it.
|     It then becomes an always-on feature with no opt-in.
|
| Keys are technical identifiers persisted in users.feature_opt_ins;
| they must stay stable and should not track user-visible naming. The
| client owns all presentation (labels, descriptions, previews) for the
| Labs page.
|
*/

return [
    /**
     * Known feature keys. The client Labs page lists the matching
     * entries; the server only needs the keys to validate opt-ins and
     * answer hasFeatureEnabled().
     */
    'beta' => [
        // Record of Review: the per-reviewer history page at
        // /account/record-of-review and its header/account-menu links.
        // Gated client-side via isFeatureEnabled('record_of_review').
        'record_of_review',
    ],
];

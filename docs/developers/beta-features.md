# Beta Features & Labs Opt-ins

Pilcrow ships work-in-progress features behind per-user **opt-ins** surfaced
on the **Labs** page (`/account/labs`). This page explains the model and walks
through adding, gating, and removing an opt-in-able feature.

## The model

Three pieces work together:

| Concept | Where it lives | What it means |
| --- | --- | --- |
| **Feature catalog** | `backend/config/features.php` (`beta` array) | The authoritative list of known feature keys. This is the **only** server-side validity gate on opting in. |
| **Opt-in record** | `users.feature_opt_ins` (JSON array) | The keys a user has turned on. **Presence of the key is the grant** — this alone decides whether a feature is enabled. |
| **Beta access** | `users.beta` (boolean, admin-set) | An **advertisement** concern only. It decides what the client *shows* in Labs, never what is *on*. |

The critical separation: **enablement is decoupled from the `beta` flag.** A
feature is on purely because its key is in `feature_opt_ins`. The `beta` flag
only controls whether a *private* feature is advertised to the user in the
Labs UI. This is what lets a future grant path (e.g. a user entering a beta
key) enable a feature for a non-beta user without advertising it to everyone.

### Server-side helpers

On `App\Models\User`:

- `User::featureExists($key)` — is the key in the catalog? Gates *opting in*.
- `$user->hasFeatureEnabled($key)` — does the user hold an active opt-in?
  **This is the gate every server code path should call.**
- `$user->getActiveFeatureOptIns()` — the raw list of opted-in keys.

The opt-in mutation is `setFeatureOptIn(feature, enabled)` (`@guard`-ed; any
authenticated user, any catalogued key). Admin beta access is granted via
`setUserBetaAccess(id, enabled)` (`@can(ability: "manageBeta")`,
application-administrator only).

### Client-side helpers

`src/use/features.ts`:

- `useFeatures()` → `isBeta`, `optedInFeatures`, `hasOptedIn(key)`,
  `isFeatureEnabled(key)`. Gate UI on `isFeatureEnabled` — it mirrors the
  server's `hasFeatureEnabled()`.
- `useLabsFeature(key)` → `optedIn`, `saving`, `toggle()`. Wraps the opt-in
  mutation for a single feature page.

The Labs page (`src/routes/account/labs.vue`) builds its list from child
routes that declare `meta.feature`, rendering each child component inline.
Each feature page wraps the shared `LabsFeaturePanel`
(`src/components/labs/LabsFeaturePanel.vue`), which renders the activate /
deactivate toggle. **The client owns all presentation** (labels,
descriptions) under the `labs.<key>` i18n namespace; the server only knows
the key.

## Adding a feature

Say you want to gate a `dark_mode` feature.

### 1. Register the key in the catalog

```php
// backend/config/features.php
'beta' => [
    'dark_mode',
],
```

### 2. Add a Labs child route on the client

Create `client/src/routes/account/labs/dark-mode.vue`:

```vue
<template>
  <labs-feature-panel feature-key="dark_mode" label="labs.dark_mode.label">
    {{ $t("labs.dark_mode.description") }}
  </labs-feature-panel>
</template>

<script setup lang="ts">
import LabsFeaturePanel from "src/components/labs/LabsFeaturePanel.vue"

definePage({
  name: "account:labs:dark-mode",
  meta: {
    // `key` matches the catalog. `private: true` hides the feature from
    // users without beta access (advertisement gate). Set `private: false`
    // for a feature you want every user to be able to opt into. `order`
    // sorts the Labs list — leave gaps (10, 20, 30) for future entries.
    feature: { key: "dark_mode", private: true, order: 10 }
  }
})
</script>
```

### 3. Add the presentation strings

```jsonc
// client/src/i18n/en-US.json → "labs"
"dark_mode": {
  "label": "Dark Mode",
  "description": "Try the new dark theme across the app."
}
```

### 4. Gate the feature

**Server** — guard any gated code path:

```php
if ($user->hasFeatureEnabled('dark_mode')) {
    // ...beta-only behavior
}
```

**Client** — gate UI on the opt-in:

```ts
const { isFeatureEnabled } = useFeatures()
const darkModeOn = isFeatureEnabled("dark_mode")
```

### 5. Cover it with tests

Backend opt-in/enablement behavior is exercised in
`backend/tests/Api/BetaFeatureTest.php` (which pins its own catalog via
`Config::set('features.beta', [...])`, so it is independent of the real
config). Client Labs surfaces are covered under `src/routes/account/`,
`src/components/labs/`, and `src/use/features.vitest.spec.ts`.

::: tip Schema snapshot
Adding a feature **key** is a config change and does not touch the GraphQL
schema. If you change the GraphQL surface (new mutation/field), regenerate the
committed snapshot — see [Backend GraphQL](./graphql-backend) — or
`SchemaSnapshotTest` will fail in CI.
:::

## Removing a feature

There are two cases.

### Graduating to general availability

When a feature is ready for everyone, it becomes always-on with no opt-in:

1. **Remove the gates.** Delete the `hasFeatureEnabled()` / `isFeatureEnabled()`
   checks so the behavior is unconditional.
2. **Remove the key** from `backend/config/features.php`.
3. **Delete the Labs child route** (`client/src/routes/account/labs/<feature>.vue`)
   and its `labs.<key>` i18n strings.

### Dropping a feature entirely

Same as above, but you delete the gated code instead of making it
unconditional.

### A note on stale opt-ins

Removing a key from the catalog does **not** clear existing
`feature_opt_ins` entries on users. `featureExists()` only gates *opting in*;
`hasFeatureEnabled()` reads the stored array regardless. Once the gated code
is gone, those orphaned keys are inert. The admin user-detail page falls back
to displaying the raw key when no `labs.<key>.label` exists. If you want to
scrub them, write a one-off migration that strips the key from every user's
`feature_opt_ins`.

## Admin: granting beta access

Application administrators advertise *private* features to specific users by
granting beta access:

- **Beta Users page** (`/admin` → Beta Users) — add or remove users in bulk.
- **User detail page** — a per-user beta toggle.

Both call `setUserBetaAccess`. Remember: this only flips advertisement
visibility. It never enables a feature on its own, and revoking it leaves a
user's existing opt-ins (and therefore their enabled features) intact.

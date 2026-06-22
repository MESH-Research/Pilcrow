# Permissions revamp: RBAC → ABAC (Bouncer)

Status: WIP design. Tracks the migration off global Spatie RBAC toward
attribute-based access control with publication- and submission-scoped
authorization.

## Why

Current state (see audit below) uses `spatie/laravel-permission` but only for
one genuinely global role (Application Administrator). Every scoped role
(publication/submission) is hand-rolled on top of pivot tables, and every
capability is a hardcoded role-id list inside policy PHP. Adding a capability
means editing a policy and redeploying. It is neither data-driven nor
expandable — the RBAC ceiling.

## Audit of the current system

- `spatie/laravel-permission ^7.0`, `teams => false`. Permissions layer
  unused (`hasPermissionTo`/`givePermissionTo` = 0 call sites). Role-assignment
  API unused (`assignRole`/`syncRoles` = 0 except the one global role).
- 6 roles in one flat table across three scopes:
  - Application: Application Administrator (id 1)
  - Publication: Publication Administrator (2), Editor (3)
  - Submission: Review Coordinator (4), Reviewer (5), Submitter (6)
- All 12 `hasRole()` calls check only `APPLICATION_ADMINISTRATOR`.
- Scoped authorization is custom: pivots `publication_user`
  (`PublicationAssignment`) and `submission_user` (`SubmissionAssignment`),
  each with `role_id`, checked via `User::hasPublicationRole()` /
  `hasSubmissionRole()` with a magic `'*'` (any-role) and role-id arrays.
- Entry point: Lighthouse `@can` (16×) → Laravel Policies
  (Publication/Submission/User). Policies are pure role-presence checks.
- Latent bug: `User::hasSubmissionRole()` single-int branch calls
  `wherePivot('role_id', null, $role)` (wrong arg shape). Dead today — all
  callers pass arrays or `'*'`.

## Decision: hybrid scoping

Native Bouncer scopes are single-level, single-active-scope multi-tenancy. Our
domain is two-level hierarchical (publication ⊃ submission), a user holds roles
in many entities at once, and one GraphQL request authorizes many entities.
Native scopes fight all three. So:

| Layer | Owns | Where |
| --- | --- | --- |
| Role assignment (scope) | who is Editor of Pub X / Reviewer of Sub Y | pivots (unchanged) |
| Ability map (RBAC core) | role → granted abilities | code (`App\Auth\ScopedRole` enum: each case returns its `Grant`s) |
| Attribute conditions | state / ownership / relationship predicates | Policy guards (thin) |

The matrix answers "can this role do X". Policy adds the attribute predicate
("…given the submission is DRAFT / you own the comment / the publication is
accepting submissions"). Ability grant + attribute predicate = ABAC.

## Ability catalog (namespaced by model)

```text
publication.view  publication.create  publication.update
submission.view   submission.create   submission.update
submission.update-status   submission.update-title
submission.update-submitters   submission.update-reviewers
submission.update-review-coordinators   submission.invite
submission.comment.update-own   submission.comment.delete-own
user.view  user.view-any  user.view-email  user.update  user.manage-beta
```

## Role → ability matrix (code)

| Ability | AppAdmin | PubAdmin | Editor | RevCoord | Submitter | Reviewer |
| --- | :-: | :-: | :-: | :-: | :-: | :-: |
| publication.update | ✓ | ✓ (own) | | | | |
| publication.view (hidden) | ✓ | ✓ | ✓ | | | |
| submission.update-submitters | ✓ | ✓ | ✓ | ✓ | ✓ | |
| submission.update-reviewers | ✓ | ✓ | ✓ | ✓ | | |
| submission.update-review-coordinators | ✓ | ✓ | ✓ | | | |
| submission.update-status | ✓ | ✓ | ✓ | ✓ | ✓* | |
| submission.update-title | ✓ | ✓ | ✓ | ✓ | ✓ | |
| submission.view / update | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| submission.invite | ✓ | ✓ | ✓ | ✓ | | |

`*` ability granted but gated to `status == DRAFT` by a policy predicate.
App Admin = Bouncer global `*` (everything).

New scoped capability = add a `Grant` to the relevant `App\Auth\ScopedRole`
case (and an `App\Auth\ScopedAbility` enum case if the ability is new). Live on
deploy; no seed, no migration.

## Attribute predicates that stay in policy

- `submission.update-status` for submitter → only while `DRAFT`.
- `submission.create` → `publication.is_accepting_submissions`.
- comment update/delete → `created_by === user.id`.
- `submission.invite` → current `getEffectiveRole()` behavior (flagged to
  simplify during migration).
- `publication.view` → `is_publicly_visible` short-circuit (no auth needed).

## Data model

- Add Bouncer tables via its published migration, namespaced `bouncer_*`
  (`bouncer_abilities`, `bouncer_permissions`, `bouncer_roles`,
  `bouncer_assigned_roles`) to coexist with spatie/laravel-permission.
- Keep `publication_user` / `submission_user` pivots as the assignment source
  of truth. The scoped roles and their ability map are code (`App\Auth\ScopedRole`,
  an int-backed enum) with no Bouncer rows. Bouncer holds only the **global**
  app-admin role row and its grant, using its own role model (we don't subclass
  it); app code names the global slug via the `App\Auth\GlobalRole` constants
  holder. Keeping scoped roles out of Bouncer means the global and scoped kinds
  are never conflated.
- The app-admin Bouncer role + `everything()` grant are created by the
  `seed_bouncer_application_admin_role` **migration** (not just a seeder), which
  also ports existing spatie application-administrators onto the Bouncer role
  before the spatie tables are dropped — so existing instances keep their admins
  across the cutover. `AbacSeeder` is the idempotent fresh-install/test equivalent.

### Pivot storage: role_id retained, FK dropped (slug column deferred)

The pivots keep their integer `role_id` column. This migration only **drops the
foreign key** to the spatie roles table (so that table can be retired); the
column itself is untouched. Authorization maps `role_id` to a slug internally:

| role_id | slug |
| --- | --- |
| 1 | application_admin |
| 2 | publication_admin |
| 3 | editor |
| 4 | review_coordinator |
| 5 | reviewer |
| 6 | submitter |

- `ScopedRole::tryFrom($roleId)` is the **live** mapping `ScopedAbilityResolver` uses
  each request to turn the pivot `role_id` into a role case — `ScopedRole` is an
  int-backed enum whose backing value *is* the `role_id`. (This lives on
  `App\Auth\ScopedRole`, not the Bouncer `Role` model.)
- GraphQL `@enum(value: …)` for `PublicationRole` / `SubmissionUserRoles` /
  `UserRoles` still map enum name → integer `role_id` (unchanged from before, so
  the client contract is untouched).
- `highest_privileged_role` remains the `min(role_id)` UI hint (a display field,
  not authz).

**Deferred follow-on:** replacing `role_id` with a human-readable `role` slug
column directly on `publication_user` / `submission_user` /
`submission_invitations` — giving one vocabulary across storage, matrix, and API
— is its own PR. It is a mechanical rename that ripples into every pivot
read/write site (models, builders, mutations, validators, GraphQL, tests); kept
out of this PR so the ABAC change stays the reviewable unit and the rename is
clear in isolation.

## The bridge

```php
// ScopedAbilityResolver: given (user, entity) -> effective ScopedRoles from pivots
//   publication: pivot roles on that publication
//   submission:  pivot roles on that submission + parent-publication admin roles
// then: does ANY effective role grant $ability (a ScopedAbility)?
$roles = $resolver->effectiveRoles($user, $submission);   // e.g. [ScopedRole::Reviewer]
return $this->allows($user, $ability, $submission);       // $role->allows(...) over its Grants
```

Policy methods shrink to `bridge.allows(ability, entity) && <predicate>`. The
`@can` directives and the 41 characterization tests are untouched.

## Migration plan (each step keeps the characterization suite green)

1. Install Bouncer, publish migration, add `HasRolesAndAbilities` to `User`
   (coexists with Spatie — different tables).
2. Create the global app-admin Bouncer role + grant via migration and port
   existing admins; scoped role→ability resolution is code, nothing to seed.
3. Build `ScopedAbilityResolver` + unit-test it against the matrix.
4. Convert PublicationPolicy (3 methods) to the bridge. Run characterization
   tests.
5. Convert SubmissionPolicy, then UserPolicy.
6. Delete `User::hasPublicationRole`/`hasSubmissionRole` (and the dead
   single-int bug) once unreferenced.
7. Optional: retire Spatie — App Admin becomes a Bouncer global role; drop
   `spatie/laravel-permission`.

## Test safety net

`tests/Feature/PublicationPolicyTest.php` and
`tests/Feature/SubmissionPolicyTest.php` (41 tests / 56 assertions) lock the
current behavior, quirks included. They assert today's truth as a regression
baseline, not ideal behavior; some quirks are flagged above as candidates to
revisit during migration.

## Where this lands, and the roadmap beyond it

The system is deliberately **hybrid**, which is the best-practice shape for
real-world authorization rather than a compromise:

- **Global concerns** → RBAC on Bouncer (app-admin today; runtime-editable
  global abilities like `publication.create` / `avatar.upload` next).
- **Scoped concerns** → role + relationship (who is Editor of Pub X) resolved
  from a code matrix, with **attribute conditions as data** on the grant.

"Be more ABAC everywhere" is a non-goal — coarse access via roles/relationships
is faster, more auditable, and easier to test; fine access via attribute
predicates layers on top. The combined decision point is the PBAC (policy-based)
ideal. What actually makes it *better* from here, in priority order:

1. **Unify list-filtering with item-authorization (correctness — highest
   value).** Query builders (`PublicationBuilder::visible()` / `myRole()`, the
   submission equivalents) reimplement authorization as SQL, parallel to the
   resolver/policies. Divergence is a latent security bug (listing what you
   cannot view, or vice versa). List-scoping and item checks should derive from
   the same role/relationship resolution. Not folded into the initial migration
   PR because doing it correctly is sizeable; it is the first follow-up.

2. **Conditions as data, not ability-name hacks (done, in this PR).** The old
   `submission.update-status-draft` encoded a state condition into an ability
   name and re-checked it in the policy. It is now a conditional grant on
   `ScopedRole::Submitter` — `[ScopedAbility::SubmissionUpdateStatus,
   SubmissionIsDraft::class]`, normalized to a `Grant` (a `ScopedAbility` plus an
   optional `Predicate`, absolute when there is none) and evaluated by the
   resolver. The policy method
   is a uniform ability check. This is the concrete ABAC improvement and the
   pattern for future state/ownership conditions. Abilities are a typed enum
   (`App\Auth\ScopedAbility`), so call sites are typo-proof and the catalog is
   greppable.

3. **Decision engines by ability type (done).** There is intentionally no single
   front door: the ability's *type* selects the engine. `GlobalAbility` →
   Bouncer (`$user->can()`, entity forwarded) at the call site; `ScopedAbility` →
   `ScopedAbilityResolver` (code-owned role/grant map, app-admin role
   short-circuit). The resolver's `allows()` is typed to `ScopedAbility`, so a
   global ability can never enter it. What remains is folding the in-policy
   attribute predicates and `@can` wiring behind a shared façade, plus decision
   logging.

4. **Explainability.** Decisions carry no trace today ("why allowed/denied").
   A decision log would aid debugging and security review. Nice-to-have.

**Non-goal for now:** a full ReBAC engine (Zanzibar / OpenFGA style). The one
relationship traversal we have — submissions inheriting parent-publication admin
roles — is hand-coded in `effectiveRoles` and adequate at one level. A
relationship-graph engine earns its place only if relationships proliferate
(teams, delegation, nested orgs, sharing). Named here as the escape hatch, not a
near-term build.

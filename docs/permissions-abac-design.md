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
|---|---|---|
| Role assignment (scope) | who is Editor of Pub X / Reviewer of Sub Y | pivots (unchanged) |
| Ability map (RBAC core) | role → granted abilities | Bouncer tables (seeded data) |
| Attribute conditions | state / ownership / relationship predicates | Policy guards (thin) |

Bouncer answers "can this role do X". Policy adds the attribute predicate
("…given the submission is DRAFT / you own the comment / the publication is
accepting submissions"). Ability grant + attribute predicate = ABAC.

## Ability catalog (namespaced by model)

```
publication.view  publication.create  publication.update
submission.view   submission.create   submission.update
submission.update-status   submission.update-title
submission.update-submitters   submission.update-reviewers
submission.update-review-coordinators   submission.invite
submission.comment.update-own   submission.comment.delete-own
user.view  user.view-any  user.view-email  user.update  user.manage-beta
```

## Role → ability matrix (the seed)

| Ability | AppAdmin | PubAdmin | Editor | RevCoord | Submitter | Reviewer |
|---|:-:|:-:|:-:|:-:|:-:|:-:|
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

New capability = add an ability + grant rows in the seed. No code change.

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
  of truth. Bouncer holds only the (small, seeded) ability map.

### Role slugs replace role_id on the pivots

The pivot `role_id` integers (FK to the spatie roles table) were obtuse and
required a translation step. They are replaced by a human-readable `role` slug
string column on `publication_user`, `submission_user`, and
`submission_invitations`; the FK to the spatie roles table is dropped.

The slugs match the GraphQL enum names, giving one vocabulary across storage,
the Bouncer ability registry, and the API:

| role_id | slug |
|---|---|
| 1 | application_admin |
| 2 | publication_admin |
| 3 | editor |
| 4 | review_coordinator |
| 5 | reviewer |
| 6 | submitter |

- GraphQL `@enum(value: …)` for `PublicationRole` / `SubmissionUserRoles` /
  `UserRoles` now map enum name → slug (the enum names are unchanged, so the
  client contract is untouched).
- The `highest_privileged_role` UI hint is a display field, not authz; its old
  `min(role_id)` ordering is replaced by an explicit `Role::SLUG_PRIORITY`
  list.
- `Role::ID_TO_SLUG` / `slugForId()` remain only to back the data migration's
  backfill (and test helpers).

## The bridge

```php
// AbilityResolver: given (user, entity) -> effective role names from pivots
//   publication: pivot roles on that publication
//   submission:  pivot roles on that submission + parent-publication admin roles
// then: does ANY effective role grant $ability in Bouncer?
$roles = $resolver->effectiveRoles($user, $submission);   // e.g. ['reviewer']
return Bouncer::role($roles)->can($ability);              // data lookup
```

Policy methods shrink to `bridge.allows(ability, entity) && <predicate>`. The
`@can` directives and the 41 characterization tests are untouched.

## Migration plan (each step keeps the characterization suite green)

1. Install Bouncer, publish migration, add `HasRolesAndAbilities` to `User`
   (coexists with Spatie — different tables).
2. Seed abilities + the role→ability matrix.
3. Build `AbilityResolver` + unit-test it against the matrix.
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

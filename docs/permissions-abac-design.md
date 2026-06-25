# Permissions: RBAC → ABAC (Bouncer)

Design rationale for Pilcrow's authorization model. The developer-facing
reference — how the pieces fit and how to use them — is
[developers/authorization.md](developers/authorization.md).

## Why

Authorization previously used `spatie/laravel-permission` for a single genuinely
global role (Application Administrator), while every scoped role
(publication/submission) was hand-rolled on pivot tables and every capability was
a hardcoded role-id list inside policy PHP. Adding a capability meant editing a
policy and redeploying — neither data-driven nor expandable, the RBAC ceiling.

## Decision: hybrid scoping

Native Bouncer scopes are single-level, single-active-scope multi-tenancy. Our
domain is two-level hierarchical (publication ⊃ submission), a user holds roles
in many entities at once, and one GraphQL request authorizes many entities.
Native scopes fight all three. So:

| Layer | Owns | Where |
| --- | --- | --- |
| Role assignment (scope) | who is Editor of Pub X / Reviewer of Sub Y | pivots |
| Ability map (RBAC core) | role → granted abilities | code (`App\Auth\Roles\ScopedRole` enum: each case returns its `Grant`s) |
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

New scoped capability = add a `Grant` to the relevant `App\Auth\Roles\ScopedRole`
case (and an `App\Auth\Abilities\SubmissionAbility` / `App\Auth\Abilities\PublicationAbility` enum
case if the ability is new). Live on deploy; no seed, no migration.

## Attribute predicates that stay in policy

- `submission.update-status` for submitter → only while `DRAFT`.
- `submission.create` → `publication.is_accepting_submissions`.
- comment update/delete → `created_by === user.id`.
- `submission.invite` → `getEffectiveRole()` behavior.
- `publication.view` → `is_publicly_visible` short-circuit (no auth needed).

## Data model

- Bouncer tables are namespaced `bouncer_*` (`bouncer_abilities`,
  `bouncer_permissions`, `bouncer_roles`, `bouncer_assigned_roles`) so they never
  clash with the retained legacy spatie `roles` / `permissions` tables (the
  cutover is expand-only; see below).
- `publication_user` / `submission_user` pivots are the assignment source of
  truth. The scoped roles and their ability map are code (`App\Auth\Roles\ScopedRole`,
  a slug-backed enum) with no Bouncer rows. Bouncer holds only the **global**
  app-admin role row and its grant, using its own role model (we don't subclass
  it); app code names the global slug via the `App\Auth\Roles\GlobalRole` slug-backed
  enum. Keeping scoped roles out of Bouncer means the global and scoped kinds are
  never conflated.
- The app-admin Bouncer role + `everything()` grant are created by the
  `seed_bouncer_application_admin_role` **migration** (not just a seeder), which
  also ports existing spatie application-administrators onto the Bouncer role — so
  existing instances keep their admins across the cutover. `AbacSeeder` is the
  idempotent fresh-install/test equivalent.
- The cutover is **expand-only**: the spatie tables are **not** dropped. They are
  left intact (admins still present in both systems) so a revert by redeploying
  the pre-slug code works without a snapshot — old code finds its spatie app-admin
  rows and the retained, dual-written pivot `role_id`. Dropping the spatie tables
  is deferred to a later **contract** PR (with the `role_id` drop).

### Pivot storage: role slug column added, legacy role_id retained

The pivots carry a human-readable `role` **slug** column — one vocabulary across
storage, the ability matrix, and the API. The `add_role_slug_to_pivots` migration
adds `role`, backfills it from the legacy `role_id` via the map below, and makes
`role_id` nullable:

| role_id (legacy) | role (slug) |
| --- | --- |
| 1 | application_admin |
| 2 | publication_admin |
| 3 | editor |
| 4 | review_coordinator |
| 5 | reviewer |
| 6 | submitter |

- `ScopedRole::tryFrom($slug)` is the **live** mapping `ScopedAbilityResolver`
  uses each request to turn the pivot `role` slug into a role case — `ScopedRole`
  is a slug-backed enum whose backing value *is* the slug.
- GraphQL `@enum(value: …)` for `PublicationRole` / `SubmissionUserRoles` maps
  enum name → slug; the wire representation is the enum **name** (e.g. `editor`),
  so the client contract is untouched. `UserRoles` keeps integer values — it is
  the `highest_privileged_role` **rank** scale, not a stored identifier.

**`role_id` retained and dual-written:** the legacy integer column stays (FK to
the spatie roles table dropped, column made nullable) as the recovery safety net.
The role relations and invite mutations write `role` and `role_id` together
(`ScopedRole::toSlug()` + `ScopedRole::legacyId()`), so a rollback to the pre-slug
code finds valid `role_id` data on rows created after the deploy — not just on
backfilled ones.

## The bridge

```php
// ScopedAbilityResolver: given (user, entity) -> effective ScopedRoles from pivots
//   publication: pivot roles on that publication
//   submission:  pivot roles on that submission + parent-publication admin roles
// then: does ANY effective role grant $ability (a ScopedAbility)?
$roles = $resolver->effectiveRoles($user, $submission);   // e.g. [ScopedRole::Reviewer]
return $this->allows($user, $ability, $submission);       // $role->allows(...) over its Grants
```

Policy methods are `allows(ability, entity) && <predicate>`; Lighthouse `@can`
directives remain the entry point.

# Authorization

Pilcrow uses **attribute-based access control (ABAC)** built on
[Bouncer](https://github.com/JosephSilber/bouncer). Authorization is decided by
three cooperating layers:

| Layer | Answers | Lives in |
| --- | --- | --- |
| Role assignment (scope) | *Who* holds *which* role *where* | pivot tables (scoped) / Bouncer (global) |
| Ability registry (RBAC core) | *What* each role may do | code (`App\Auth\Roles\ScopedRole` grants) + Bouncer (global) |
| Attribute predicates | State / ownership conditions | Laravel policies |

A grant is the conjunction of all three: an ability granted to one of the
user's effective roles **and** any attribute predicate on the policy method.

## Roles

Six roles span three scopes, but they are **two different kinds of thing** with
two homes:

| Slug | Scope | Kind | Defined in | Assigned via |
| --- | --- | --- | --- | --- |
| `application_admin` | Application (global) | Bouncer role | Bouncer's `Role` model (slug in the `App\Auth\Roles\GlobalRole` enum) | Bouncer `assigned_roles` |
| `publication_admin` | Publication | scoped (code) | `App\Auth\Roles\ScopedRole` | `publication_user` pivot |
| `editor` | Publication | scoped (code) | `App\Auth\Roles\ScopedRole` | `publication_user` pivot |
| `review_coordinator` | Submission | scoped (code) | `App\Auth\Roles\ScopedRole` | `submission_user` pivot |
| `reviewer` | Submission | scoped (code) | `App\Auth\Roles\ScopedRole` | `submission_user` pivot |
| `submitter` | Submission | scoped (code) | `App\Auth\Roles\ScopedRole` | `submission_user` pivot |

Only `application_admin` is a real Bouncer role with a row in `bouncer_roles`.
The five scoped roles are a typed code catalog — the `App\Auth\Roles\ScopedRole`
**enum** — with **no** Bouncer rows; they are never assigned through Bouncer.
Keeping them out of the Bouncer role model is deliberate: it stops a scoped role
from being mistaken for, or assigned as, a global one.

### Scoped roles (pivots)

Publication and submission roles are stored on the `publication_user` and
`submission_user` pivots in a `role` **slug** column. `ScopedRole` is a
**string-backed enum whose backing value is that slug** (`publication_admin`,
`editor`, …), so the pivot value maps straight to a case. The relations encode
the role:

```php
// App\Models\Publication
$this->users()->withPivotValue('role', ScopedRole::Editor->toSlug()); // editors()
```

`ScopedAbilityResolver` maps the pivot `role` slug to a case via
`ScopedRole::tryFrom($slug)` (unknown slugs are skipped). The slug is the one
vocabulary shared across storage, the ability matrix, and the API.

The legacy integer `role_id` column is **retained and dual-written**: its foreign
key to the old spatie roles table is dropped, it is made nullable, and the `role`
slug is backfilled from it. The role relations and invite mutations write `role`
and `role_id` together (`ScopedRole::toSlug()` + `ScopedRole::legacyId()`),
so `role_id` stays valid on new rows — a rollback to the pre-slug code still finds
usable data. `role_id` is kept solely as that safety net; dropping it is a later
PR once the slug column is proven in production.

A user can hold many scoped roles across many entities simultaneously, which is
why scoping lives in pivots rather than a single Bouncer scope.

### Global role (Bouncer)

`application_admin` is the one global role. It is a real Bouncer role assigned
through `assigned_roles`; `User` uses Bouncer's `HasRolesAndAbilities` trait.
Check it with:

```php
$user->isApplicationAdministrator(); // === $user->isA(GlobalRole::ApplicationAdministrator->toSlug())
```

The role row, its id, and assignments are owned entirely by Bouncer (its own
`Silber\Bouncer\Database\Role` model — we do **not** subclass it). App code only
names the slug via the `App\Auth\Roles\GlobalRole` **slug-backed enum** (`toSlug()` is
the backing value; `title()` is `@deprecated` in favour of i18n from the slug).
`application_admin`
is granted `everything()`, so it satisfies every **global** ability check and,
as a role, short-circuits every **scoped** check.

## Ability registry

Abilities are **typed, closed catalogs**. The first split is by *engine*:
**scoped** vs **global** — the type alone says which engine answers. Scoped
abilities are then split again by the *model* they act on, purely for
organization:

- **`App\Auth\Abilities\ScopedAbility`** — a marker interface for abilities resolved
  against a publication / submission by the code-owned role/grant map, via
  `ScopedAbilityResolver`. Its cases live on two enums that implement it, split
  by model: **`App\Auth\Abilities\SubmissionAbility`** (`SubmissionAbility::UpdateStatus`,
  `SubmissionAbility::View`, …) and **`App\Auth\Abilities\PublicationAbility`**
  (`PublicationAbility::Update`, `PublicationAbility::View`). Either enum's case
  is a valid scoped ability; the interface is the type everything accepts.
- **`App\Auth\Abilities\GlobalAbility`** — application-wide abilities resolved by Bouncer
  via `$user->can()` (`GlobalAbility::PublicationCreate`,
  `GlobalAbility::UserViewAny`, …).

Both back onto the legacy dotted string. Policies reference enum cases, not magic
strings, so a typo is a compile-time error and "who grants this?" is a
find-usages. The split is also a safety boundary: `ScopedAbilityResolver::allows()`
is typed to `ScopedAbility` and throws on anything else, and global checks never
enter it — so a Bouncer grant can never short-circuit a scoped check. Only the
app-admin role does that, explicitly.

The scoped role → ability map is **code on the `App\Auth\Roles\ScopedRole` enum**:
each case lists its grants in shorthand — a bare `Ability` is an absolute grant,
an `[Ability, PredicateClass]` pair a conditional one — which `grants()`
normalizes to `App\Auth\Grants\Grant` objects. A `Grant` pairs an `Ability` with an
optional `Predicate`; the predicate lives on the *grant* (the role↔ability
pairing), not the ability, because the same ability is absolute for one role and
conditional for another. No predicate = absolute grant. Roles compose as
supersets by spreading the role below them:

```php
private function grantDefinitions(): array
{
    return match ($this) {
        self::Reviewer => [
            SubmissionAbility::View,                       // absolute
            SubmissionAbility::Update,
        ],
        self::Submitter => [
            ...self::Reviewer->grantDefinitions(),         // everything a reviewer has…
            SubmissionAbility::UpdateTitle,                // …plus these…
            // …and a conditional grant: status only while DRAFT
            [SubmissionAbility::UpdateStatus, SubmissionIsDraft::class],
        ],
        // ...
    };
}
```

A `Predicate` (e.g. `App\Auth\Grants\Predicates\SubmissionIsDraft`) is a small reusable
value object — `holds(Model $entity, User $user): bool` — unit-testable in
isolation and shareable across grants; `grants()` instantiates it from the
class-string. It is read directly by `ScopedAbilityResolver` at request time — there
is no DB round-trip and nothing to seed. Adding a scoped capability is a code
change: add an entry to the relevant case(s); live on deploy, no seeding,
convergence, or drift. Scoped abilities are intentionally **not**
runtime-editable.

`application_admin` has no scoped grant entry — it is a real Bouncer role granted
`everything()`, short-circuited by role for scoped checks and satisfied by the
wildcard for global ones. Its Bouncer role row (with the human-readable `title`
used by the GraphQL `Role.name` field) and `everything()` grant are established
on deploy by the `seed_bouncer_application_admin_role` **migration**, which also
ports any existing spatie application-administrators onto the Bouncer role before
the spatie tables are dropped. `Database\Seeders\AbacSeeder` does the same thing
idempotently for fresh installs and the test bootstrap. The Bouncer tables are
namespaced `bouncer_*` (set in `AppServiceProvider`); Bouncer uses its own role
model — we don't subclass it.

## The two engines

There is no single front door, by design. Each ability kind is checked by the
engine that owns it:

- **`GlobalAbility`** → Bouncer, called directly at the policy:
  `$viewer->can(GlobalAbility::UserViewAny)`. The app administrator passes via
  its `everything()` wildcard; others only if granted at the Bouncer layer. This
  never touches the scoped resolver.
- **`ScopedAbility`** → `App\Auth\ScopedAbilityResolver`, injected into the
  policy: `$this->scoped->allows($user, SubmissionAbility::UpdateStatus, $submission)`.

`ScopedAbilityResolver` only gets involved when a publication or submission is in
play. Its `allows()` is typed `ScopedAbility` — passing anything else (a
`GlobalAbility`, a string) is a `TypeError`, so it can never be tricked into
answering a global question. It:

1. short-circuits on the app-admin **role** (never a Bouncer ability), then
2. resolves the user's **effective `ScopedRole`s** for the entity (reading
   the `role` slug from the pivots, mapping each via `ScopedRole::tryFrom()`):
   - For a `Publication`: the user's `publication_user` roles on it.
   - For a `Submission`: the user's `submission_user` roles on it **plus** the
     admin roles inherited from the parent publication (publication admin /
     editor).
3. asks each effective role `$role->allows($ability, $entity, $user)` — a plain
   in-memory check over that role's `Grant`s, no database access, evaluating any
   predicate against the entity.

## List filtering

Listing endpoints can't loop the resolver per row, so they need the same
decision expressed as SQL. Rather than hand-roll a parallel rule set, the
builders derive their WHERE clause from the **same matrix**:

- `ScopedRole::rolesGranting($ability)` inverts the matrix — the roles that grant
  an ability — and `grantingSlugsFor($ability, $pivot)` narrows that to the role
  slugs on one pivot. `ScopedRole::pivot()` says which pivot a role lives on.
- `PublicationBuilder::whereCan($ability)` / `SubmissionBuilder::whereCan($ability)`
  build the membership filter from those slugs, with the same app-admin bypass
  and (for submissions) the same parent-publication inheritance the resolver
  applies. `Builder::visible()` delegates to `whereCan(...View)`.

Because both the resolver and the builders read `rolesGranting()`, item checks
and list scoping cannot diverge — `ScopedAbilityListParityTest` asserts the
listed set equals the per-item resolver verdict. List-filtering is limited to
**unconditional** abilities: a `Predicate` has no SQL form, so `rolesGranting()`
throws on a conditional grant rather than filter incorrectly (the conditions
that are list-filterable in SQL are a later, Tier 2 addition giving `Predicate` a
`scope()` form).

## Policies

Lighthouse `@can` directives are the entry point and resolve to Laravel
policies (`PublicationPolicy`, `SubmissionPolicy`, `UserPolicy`). Policies with
scoped checks inject `ScopedAbilityResolver`; a scoped method is a one-line
check:

```php
public function updateReviewers(User $user, Submission $submission)
{
    return $this->scoped->allows($user, SubmissionAbility::UpdateReviewers, $submission)
        ? true
        : Response::deny('UNAUTHORIZED');
}
```

A global method skips the resolver and asks Bouncer directly:

```php
public function viewAny(User $viewer): bool
{
    return $viewer->can(GlobalAbility::UserViewAny);
}
```

**Attribute predicates** that depend on entity state, ownership, or
relationships stay in the policy, layered on the ability check:

- Submitters may change a submission's status only while it is `DRAFT` — modeled
  as a **conditional grant** on `ScopedRole::Submitter`
  (`[SubmissionAbility::UpdateStatus, SubmissionIsDraft::class]`). The condition
  is a `Predicate` object on the grant, not a special ability name or a policy
  branch.
- Comment edit/delete require `created_by === user.id`.
- `submission.create` is role-agnostic: allowed when the publication is
  accepting submissions.
- `publication.view` short-circuits to allowed for publicly visible
  publications (no auth needed).
- `UserPolicy::viewEmail` is a relationship predicate: the viewer must share a
  publication (as admin/editor) with the target.

## The `highest_privileged_role` field

`User::getHighestPrivilegedRole()` returns the most-privileged **rank** the
user holds anywhere (lower ranks higher: `application_admin`=1 … `submitter`=6),
computed from `GlobalRole::rank()` / `ScopedRole::rank()`. The rank ints are the
GraphQL `UserRoles` enum values. It is a **client UI hint** (routing, which
dashboard table to show) — not an authorization mechanism.

## Exposing abilities to the client

The client should gate navigation/UI on **decisions, not rules** — it must not
re-implement the role matrix. Each GraphQL type carries an `abilities` field
resolved through the **same engines** as the policies, so the flags can never
drift from real authorization:

| Type          | Field       | Resolver method      | Engine                     |
| ------------- | ----------- | -------------------- | -------------------------- |
| `User`        | `abilities` | `globalAbilities()`  | Bouncer (`$user->can`)     |
| `Publication` | `abilities` | `abilities()`        | `ScopedAbilityResolver`    |
| `Submission`  | `abilities` | `abilities()`        | `ScopedAbilityResolver`    |

Each returns an object of `snake_case ability => Boolean!`, one field per enum
case (`GlobalAbility` / `PublicationAbility` / `SubmissionAbility`). The PHP
methods build the map by iterating `Ability::cases()` and `Str::snake()`-ing the
case name, so a new enum case only needs its matching GraphQL field added.

```graphql
query {
  currentUser { abilities { publication_create user_view_any } }
  submission(id: 1) {
    abilities { view update update_status update_reviewers }
  }
}
```

Semantics:

- `User.abilities` is that user's global abilities; fetched via `currentUser`
  it is the **viewer's** own. Scoped `abilities` are always the **authenticated
  viewer's** abilities on that entity (guests get all-`false`).
- Scoped flags honor **conditional grants**: a submitter's `update_status` is
  `true` only while the submission is a draft, because the resolver evaluates the
  predicate against the entity. A role-string flag (the deprecated
  `effective_role`) cannot express this — these flags replace that gating.
- **UI hints only.** The server still enforces every mutation with `@can`;
  hiding a control is not a security boundary.

## Recipes

**Add a scoped capability to a role:** add a `Grant` (and, if new, a
`SubmissionAbility` / `PublicationAbility` enum case) to the relevant
`ScopedRole` case's `grants()`. It is live on deploy — no re-seed, no migration.

**Add a global capability:** add a `GlobalAbility` enum case and check it with
`allows($user, GlobalAbility::X)`; grant it to roles/users at the Bouncer layer.
(Today the app administrator holds all of them via `everything()`.)

**Authorize in a resolver/policy:** inject `ScopedAbilityResolver` and call
`allows($user, SubmissionAbility::View, $entity)` (or a `PublicationAbility`
case); add any state/ownership predicate as a `Predicate` on the grant, or inline
in the policy for one-offs.

**Assign roles:**

```php
$user->assignRole(GlobalRole::ApplicationAdministrator);      // global (Bouncer)
$publication->editors()->attach($user);                       // scoped (pivot)
$submission->users()->attach($user->id, ['role' => ScopedRole::Reviewer->toSlug()]);
```

**In tests:** the base `Tests\TestCase` seeds `AbacSeeder` after each database
refresh, so the app-admin role row and its grant are present. Scoped ability
resolution needs no seeding — it reads the code matrix. Use `beAppAdmin()` and
the `attachTo*` helpers to set up actors.

## History

The design rationale and the migration from the previous Spatie role-based
system are recorded in
[permissions-abac-design.md](../permissions-abac-design.md).

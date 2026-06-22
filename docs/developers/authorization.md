# Authorization

Pilcrow uses **attribute-based access control (ABAC)** built on
[Bouncer](https://github.com/JosephSilber/bouncer). Authorization is decided by
three cooperating layers:

| Layer | Answers | Lives in |
| --- | --- | --- |
| Role assignment (scope) | *Who* holds *which* role *where* | pivot tables |
| Ability registry (RBAC core) | *What* each role may do | code matrix (`App\Auth\RoleAbilities`) |
| Attribute predicates | State / ownership conditions | Laravel policies |

A grant is the conjunction of all three: an ability granted to one of the
user's effective roles **and** any attribute predicate on the policy method.

## Roles

Six roles span three scopes, but they are **two different kinds of thing** with
two homes:

| Slug | Scope | Kind | Defined in | Assigned via |
| --- | --- | --- | --- | --- |
| `application_admin` | Application (global) | Bouncer role | `App\Models\Role` | Bouncer `assigned_roles` |
| `publication_admin` | Publication | scoped (code) | `App\Auth\ScopedRole` | `publication_user` pivot |
| `editor` | Publication | scoped (code) | `App\Auth\ScopedRole` | `publication_user` pivot |
| `review_coordinator` | Submission | scoped (code) | `App\Auth\ScopedRole` | `submission_user` pivot |
| `reviewer` | Submission | scoped (code) | `App\Auth\ScopedRole` | `submission_user` pivot |
| `submitter` | Submission | scoped (code) | `App\Auth\ScopedRole` | `submission_user` pivot |

Only `application_admin` is a real Bouncer role with a row in `bouncer_roles`.
The five scoped roles are a typed code catalog — the `App\Auth\ScopedRole`
**enum** — with **no** Bouncer rows; they are never assigned through Bouncer.
Keeping them out of the Bouncer role model is deliberate: it stops a scoped role
from being mistaken for, or assigned as, a global one.

### Scoped roles (pivots)

Publication and submission roles are stored on the `publication_user` and
`submission_user` pivots as an integer `role_id` column (the foreign key to the
old spatie roles table has been dropped). `ScopedRole` is an **int-backed enum
whose backing value is that `role_id`**, so the pivot value maps straight to a
case. The relations encode the role:

```php
// App\Models\Publication
$this->users()->withPivotValue('role_id', ScopedRole::Editor->value); // editors()
```

`AbilityResolver` maps the pivot `role_id` to a case via
`ScopedRole::tryFrom($roleId)` (unknown ids are skipped). Replacing `role_id`
with a human-readable slug column directly on the pivots is a deliberately
deferred follow-on PR — it touches every pivot read/write site and is clearer
reviewed in isolation.

A user can hold many scoped roles across many entities simultaneously, which is
why scoping lives in pivots rather than a single Bouncer scope.

### Global role (Bouncer)

`application_admin` is the one global role. It is a real Bouncer role assigned
through `assigned_roles`; `User` uses Bouncer's `HasRolesAndAbilities` trait.
Check it with:

```php
$user->isApplicationAdministrator(); // === $user->isA(Role::SLUG_APPLICATION_ADMIN)
```

`application_admin` is granted `everything()`, so it short-circuits every
ability check.

## Ability registry

Abilities are a **typed, closed catalog** — the `App\Auth\Ability` enum
(`Ability::SubmissionUpdateStatus`, `Ability::PublicationUpdate`, …; the backing
value is the legacy dotted string). Policies reference enum cases, not magic
strings, so a typo is a compile-time error and "who grants this?" is a
find-usages.

The scoped role → ability map is **code on the `App\Auth\ScopedRole` enum**:
each case lists its grants in shorthand — a bare `Ability` is an absolute grant,
an `[Ability, PredicateClass]` pair a conditional one — which `grants()`
normalizes to `App\Auth\Grant` objects. A `Grant` pairs an `Ability` with an
optional `Predicate`; the predicate lives on the *grant* (the role↔ability
pairing), not the ability, because the same ability is absolute for one role and
conditional for another. No predicate = absolute grant. Roles compose as
supersets by spreading the role below them:

```php
private function grantDefinitions(): array
{
    return match ($this) {
        self::Reviewer => [
            Ability::SubmissionView,                       // absolute
            Ability::SubmissionUpdate,
        ],
        self::Submitter => [
            ...self::Reviewer->grantDefinitions(),         // everything a reviewer has…
            Ability::SubmissionUpdateTitle,                // …plus these…
            // …and a conditional grant: status only while DRAFT
            [Ability::SubmissionUpdateStatus, SubmissionIsDraft::class],
        ],
        // ...
    };
}
```

A `Predicate` (e.g. `App\Auth\Predicates\SubmissionIsDraft`) is a small reusable
value object — `holds(Model $entity, User $user): bool` — unit-testable in
isolation and shareable across grants; `grants()` instantiates it from the
class-string. It is read directly by `AbilityResolver` at request time — there
is no DB round-trip and nothing to seed. Adding a scoped capability is a code
change: add an entry to the relevant case(s); live on deploy, no seeding,
convergence, or drift. Scoped abilities are intentionally **not**
runtime-editable.

`application_admin` is the exception — it is a real Bouncer role granted
`everything()` and short-circuited in the resolver, so it has no matrix entry.
Its Bouncer role row (with the human-readable `title` used by the GraphQL
`Role.name` field) and `everything()` grant are established on deploy by the
`seed_bouncer_application_admin_role` **migration**, which also ports any
existing spatie application-administrators onto the Bouncer role before the
spatie tables are dropped. `Database\Seeders\AbacSeeder` does the same thing
idempotently for fresh installs and the test bootstrap. The Bouncer tables are
namespaced `bouncer_*` — set in `AppServiceProvider`, which also points Bouncer
at `App\Models\Role` via `Bouncer::useRoleModel()`.

## The resolver

`App\Auth\AbilityResolver` joins the pivot-scoped assignment to the code-owned
role definitions:

```php
$resolver->allows($user, Ability::SubmissionUpdateStatus, $submission);
```

It short-circuits to allowed if the user holds the global `application_admin`
role, then resolves the user's **effective `ScopedRole`s** for the entity
(reading `role_id` from the pivots and mapping each via `ScopedRole::tryFrom()`):

- For a `Publication`: the user's `publication_user` roles on it.
- For a `Submission`: the user's `submission_user` roles on it **plus** the
  admin roles inherited from the parent publication (publication admin /
  editor).

It then asks each effective role `$role->allows($ability, $entity, $user)` — a
plain in-memory check over that role's `Grant`s, no database access, evaluating
any predicate against the entity.

## Policies

Lighthouse `@can` directives are the entry point and resolve to Laravel
policies (`PublicationPolicy`, `SubmissionPolicy`, `UserPolicy`), which inject
`AbilityResolver`. A typical method is a one-line ability check:

```php
public function updateReviewers(User $user, Submission $submission)
{
    return $this->abilities->allows($user, Ability::SubmissionUpdateReviewers, $submission)
        ? true
        : Response::deny('UNAUTHORIZED');
}
```

**Attribute predicates** that depend on entity state, ownership, or
relationships stay in the policy, layered on the ability check:

- Submitters may change a submission's status only while it is `DRAFT` — modeled
  as a **conditional grant** on `ScopedRole::Submitter`
  (`[Ability::SubmissionUpdateStatus, SubmissionIsDraft::class]`). The condition
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

`User::getHighestPrivilegedRole()` returns the most-privileged `role_id` the
user holds anywhere (lowest id ranks highest: `application_admin`=1 …
`submitter`=6). It is a **client UI hint** (routing, which dashboard table to
show) — not an authorization mechanism.

## Recipes

**Add a capability to a role:** add a `Grant` (and, if new, an `Ability` enum
case) to the relevant `ScopedRole` case's `grants()`. It is live on deploy — no
re-seed, no migration.

**Authorize in a resolver/policy:** inject `AbilityResolver` and call
`allows($user, Ability::SomeCase, $entity)`; add any state/ownership predicate
as a `Predicate` on the grant, or inline in the policy for one-offs.

**Assign roles:**

```php
$user->assignRole(Role::APPLICATION_ADMINISTRATOR);          // global (Bouncer)
$publication->editors()->attach($user);                       // scoped (pivot)
$submission->users()->attach($user->id, ['role_id' => ScopedRole::Reviewer->value]);
```

**In tests:** the base `Tests\TestCase` seeds `AbacSeeder` after each database
refresh, so the app-admin role row and its grant are present. Scoped ability
resolution needs no seeding — it reads the code matrix. Use `beAppAdmin()` and
the `attachTo*` helpers to set up actors.

## History

The design rationale and the migration from the previous Spatie role-based
system are recorded in
[permissions-abac-design.md](../permissions-abac-design.md).

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
The five scoped roles are a static code catalog (`App\Auth\ScopedRole`) — they
have **no** Bouncer rows and are never assigned through Bouncer. Keeping them out
of the Bouncer role model is deliberate: it stops a scoped role from being
mistaken for, or assigned as, a global one. Slugs match the GraphQL enum names.

### Scoped roles (pivots)

Publication and submission roles are stored on the `publication_user` and
`submission_user` pivots as an integer `role_id` column (the foreign key to the
old spatie roles table has been dropped). The relations encode the role:

```php
// App\Models\Publication
$this->users()->withPivotValue('role_id', ScopedRole::EDITOR_ROLE_ID); // editors()
```

The authorization layer works in **slugs** (the ability matrix is keyed by
them); `AbilityResolver` maps the pivot `role_id` to a slug via
`ScopedRole::slugForId()` (`ScopedRole::ID_TO_SLUG`). Replacing `role_id` with a
human-readable slug column directly on the pivots is a deliberately deferred
follow-on PR — it touches every pivot read/write site and is clearer reviewed
in isolation.

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

Abilities are granular, namespaced capability strings (e.g.
`publication.update`, `submission.update-status`, `submission.invite`). The
scoped role → ability map is **code**, the single source of truth in
`App\Auth\RoleAbilities::matrix()`. Each role maps to a list of grants; a grant
is either a bare ability string (absolute) or `ability => predicate` (granted
only when the predicate holds for the entity):

```php
public static function matrix(): array
{
    return [
        ScopedRole::SLUG_SUBMITTER => [
            'submission.view',                 // absolute grant
            'submission.update-title',
            // conditional: allowed only while the submission is DRAFT
            'submission.update-status' => static fn ($s) => $s->status === Submission::DRAFT,
        ],
        // ...
    ];
}
```

It is read directly by `AbilityResolver` at request time — there is no DB
round-trip and nothing to seed. Adding a scoped capability is a code change:
add the ability name to the relevant role(s) in `matrix()`; it is live on
deploy, with no seeding, convergence, or drift. Scoped abilities are
intentionally **not** runtime-editable.

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
ability matrix:

```php
$resolver->allows($user, 'submission.update-status', $submission);
```

It resolves the user's **effective role slugs** for the entity (reading
`role_id` from the pivots and mapping each to its slug):

- `application_admin` if the user holds the global role (→ allowed, short-circuit).
- For a `Publication`: the user's `publication_user` roles on it.
- For a `Submission`: the user's `submission_user` roles on it **plus** the
  admin roles inherited from the parent publication (publication admin /
  editor).

It then asks `RoleAbilities::grants($role, $ability, $entity)` for each
effective role — a plain in-memory lookup, no database access. `grants`
resolves both absolute and conditional grants, evaluating any predicate against
the entity.

## Policies

Lighthouse `@can` directives are the entry point and resolve to Laravel
policies (`PublicationPolicy`, `SubmissionPolicy`, `UserPolicy`), which inject
`AbilityResolver`. A typical method is a one-line ability check:

```php
public function updateReviewers(User $user, Submission $submission)
{
    return $this->abilities->allows($user, 'submission.update-reviewers', $submission)
        ? true
        : Response::deny('UNAUTHORIZED');
}
```

**Attribute predicates** that depend on entity state, ownership, or
relationships stay in the policy, layered on the ability check:

- Submitters may change a submission's status only while it is `DRAFT` — modeled
  as a **conditional grant** in `RoleAbilities::matrix()`
  (`submission.update-status` for submitter carries a `status == DRAFT`
  predicate the resolver evaluates). The condition is data on the grant, not a
  special ability name or a policy branch.
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

**Add a capability to a role:** add the ability string to the role's list in
`App\Auth\RoleAbilities::matrix()`. It is live on deploy — no re-seed, no
migration.

**Authorize in a resolver/policy:** inject `AbilityResolver` and call
`allows($user, $ability, $entity)`; add any state/ownership predicate inline.

**Assign roles:**

```php
$user->assignRole(Role::APPLICATION_ADMINISTRATOR);          // global (Bouncer)
$publication->editors()->attach($user);                       // scoped (pivot)
$submission->users()->attach($user->id, ['role_id' => ScopedRole::REVIEWER_ROLE_ID]);
```

**In tests:** the base `Tests\TestCase` seeds `AbacSeeder` after each database
refresh, so the app-admin role row and its grant are present. Scoped ability
resolution needs no seeding — it reads the code matrix. Use `beAppAdmin()` and
the `attachTo*` helpers to set up actors.

## History

The design rationale and the migration from the previous Spatie role-based
system are recorded in
[permissions-abac-design.md](../permissions-abac-design.md).

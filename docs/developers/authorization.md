# Authorization

Pilcrow uses **attribute-based access control (ABAC)** built on
[Bouncer](https://github.com/JosephSilber/bouncer). Authorization is decided by
three cooperating layers:

| Layer | Answers | Lives in |
| --- | --- | --- |
| Role assignment (scope) | *Who* holds *which* role *where* | pivot tables |
| Ability registry (RBAC core) | *What* each role may do | Bouncer (`bouncer_*` tables) |
| Attribute predicates | State / ownership conditions | Laravel policies |

A grant is the conjunction of all three: an ability granted to one of the
user's effective roles **and** any attribute predicate on the policy method.

## Roles

Six roles span three scopes. They are identified everywhere by a **slug**
(`App\Models\Role::SLUG_*`) that matches the GraphQL enum names:

| Slug | Scope | Assigned via |
| --- | --- | --- |
| `application_admin` | Application (global) | Bouncer `assigned_roles` |
| `publication_admin` | Publication | `publication_user` pivot |
| `editor` | Publication | `publication_user` pivot |
| `review_coordinator` | Submission | `submission_user` pivot |
| `reviewer` | Submission | `submission_user` pivot |
| `submitter` | Submission | `submission_user` pivot |

### Scoped roles (pivots)

Publication and submission roles are stored on the `publication_user` and
`submission_user` pivots as a `role` slug column (there is no foreign key to a
roles table). The relations encode the slug:

```php
// App\Models\Publication
$this->users()->withPivotValue('role', Role::SLUG_EDITOR); // editors()
```

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
role → ability map is **data**, seeded by `Database\Seeders\AbacSeeder::MATRIX`:

```php
public const MATRIX = [
    Role::SLUG_EDITOR => [
        'publication.view',
        'submission.update-reviewers',
        // ...
    ],
    // ...
];
```

Adding a capability is a data change — add the ability name to the relevant
role(s) in `MATRIX`; no policy code changes. Roles are seeded with a
human-readable `title` (e.g. "Application Administrator") used by the GraphQL
`Role.name` field.

The Bouncer tables are namespaced `bouncer_*` (`bouncer_abilities`,
`bouncer_roles`, `bouncer_assigned_roles`, `bouncer_permissions`) — set in
`AppServiceProvider`, which also points Bouncer at `App\Models\Role` via
`Bouncer::useRoleModel()`.

## The resolver bridge

`App\Auth\AbilityResolver` joins the pivot-scoped assignment to the Bouncer
ability map:

```php
$resolver->allows($user, 'submission.update-status', $submission);
```

It resolves the user's **effective role slugs** for the entity:

- `application_admin` if the user holds the global role (→ allowed, short-circuit).
- For a `Publication`: the user's `publication_user` roles on it.
- For a `Submission`: the user's `submission_user` roles on it **plus** the
  admin roles inherited from the parent publication (publication admin /
  editor).

It then asks Bouncer whether any effective role grants the ability. Role →
ability lookups are memoized per resolver instance.

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
  with a distinct `submission.update-status-draft` ability gated on status.
- Comment edit/delete require `created_by === user.id`.
- `submission.create` is role-agnostic: allowed when the publication is
  accepting submissions.
- `publication.view` short-circuits to allowed for publicly visible
  publications (no auth needed).
- `UserPolicy::viewEmail` is a relationship predicate: the viewer must share a
  publication (as admin/editor) with the target.

## The `highest_privileged_role` field

`User::getHighestPrivilegedRole()` returns the most-privileged role slug the
user holds anywhere. It is a **client UI hint** (routing, which dashboard table
to show) — not an authorization mechanism. Ordering comes from
`Role::SLUG_PRIORITY`.

## Recipes

**Add a capability to a role:** add the ability string to the role's list in
`AbacSeeder::MATRIX`, then re-seed (`lando artisan db:seed --class=AbacSeeder`).

**Authorize in a resolver/policy:** inject `AbilityResolver` and call
`allows($user, $ability, $entity)`; add any state/ownership predicate inline.

**Assign roles:**

```php
$user->assignRole(Role::APPLICATION_ADMINISTRATOR);          // global (Bouncer)
$publication->editors()->attach($user);                       // scoped (pivot)
$submission->users()->attach($user->id, ['role' => Role::SLUG_REVIEWER]);
```

**In tests:** the base `Tests\TestCase` seeds `AbacSeeder` after each database
refresh, so the ability registry is always present. Use `beAppAdmin()` and the
`attachTo*` helpers to set up actors.

## History

The design rationale and the migration from the previous Spatie role-based
system are recorded in
[permissions-abac-design.md](../permissions-abac-design.md).

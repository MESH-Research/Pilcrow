# BreadCrumbs

`BreadCrumbs` renders the page's breadcrumb trail. It builds the
trail by walking the active route's matched records and collecting
each record's `meta.crumb` declaration, so individual pages and
layouts opt in declaratively — there is no central registry to
maintain.

The component itself is a thin wrapper around Quasar's
[`q-breadcrumbs`](https://quasar.dev/vue-components/breadcrumbs).
Most configuration happens on the route definition.

## Declaring a crumb

In a `definePage` block, set `meta.crumb`:

```vue
<script setup lang="ts">
definePage({
  name: "admin:users",
  meta: {
    crumb: { label: "breadcrumbs.admin.users" }
  }
})
</script>
```

`label` is treated as an i18n key — `BreadCrumbs` calls `$t(label)`
when resolving the trail. Use the existing `breadcrumbs.*` namespace
in `client/src/i18n/en-US.json`.

### Crumb fields

| Field   | Type               | Description                                                                                                                                                                |
| ------- | ------------------ | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `label` | `string`           | i18n key. Resolved via `$t`.                                                                                                                                               |
| `to`    | `RouteLocationRaw` | Optional link target. Defaults to the route's own location. The final crumb in the trail typically does not link anywhere — Quasar renders it as plain text automatically. |
| `icon`  | `string`           | Optional Quasar icon name rendered before the label.                                                                                                                       |

## Stacking multiple crumbs from one route

A single `meta.crumb` may be an **array** to contribute multiple
crumbs from one route record. This avoids creating intermediate
layout files purely for breadcrumb anchoring:

```ts
definePage({
  name: "admin:user:id",
  meta: {
    crumb: [
      { label: "breadcrumbs.admin.users", to: { name: "admin:users" } },
      { label: "breadcrumbs.admin.user" }
    ]
  }
})
```

The example above renders `Administration › Users › User` while
keeping `Users` as a link. Only the **last** crumb in the array
accepts a dynamic label override (see below) — preceding entries
are treated as static parent links.

## Dynamic labels

When a crumb's label depends on fetched data (a user's name, a
publication's title), declare a placeholder key in `meta.crumb` and
override it at runtime with `setCrumbLabel`:

```ts
import { setCrumbLabel } from "src/use/breadcrumbs"

const { result } = useQuery(getUserDetailDocument, ...)
const user = computed(() => result.value?.user)

setCrumbLabel(
  "admin:user:id",
  computed(() => user.value?.name || user.value?.username || undefined)
)
```

`setCrumbLabel`:

- Targets a crumb by **route name**, not by index.
- Accepts a `MaybeRef<string | undefined>`. Returning `undefined`
  falls back to the i18n key in `meta.crumb`.
- Applies to the **last** crumb contributed by that route — in the
  array form above, that's the user-name slot.

## Suppressing a crumb

Routes that should not contribute a crumb (deep child routes that
duplicate the parent's last rung, modal-style detail panes that
share their parent's URL) can omit `meta.crumb` entirely or set it
to `false`:

```ts
definePage({
  name: "user_details",
  // No crumb — the parent layout already stacks "Users › {name}".
  meta: {}
})
```

## Rendering

Drop `<BreadCrumbs />` into a layout. It hides itself when the
trail is empty, so it is safe to render unconditionally:

```vue
<template>
  <BreadCrumbs />
  <router-view />
</template>

<script setup lang="ts">
import BreadCrumbs from "src/components/BreadCrumbs.vue"
</script>
```

## Programmatic access

If a component needs the trail outside of `BreadCrumbs`, call
`useCrumbs()` directly:

```ts
import { useCrumbs } from "src/use/breadcrumbs"

const { crumbs, count } = useCrumbs()
```

`crumbs` is a `ResolvedCrumb[]` ref; each entry has a reactive
`label` ref, a resolved `to` location, and an optional `icon`.

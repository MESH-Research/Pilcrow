# Client GraphQL Conventions

This documents the conventions for colocated GraphQL fragments and queries in Vue SFCs. For general GraphQL codegen setup and TypeScript type usage, see the [TypeScript documentation](./typescript).

## Data Fetching Architecture

```
Pages (queries/mutations + useQuery/useMutation)
  └─ Data components (fragments — declare fields, never fetch)
       └─ Pure UI components (no GraphQL — plain TS props)
```

**Pages** are the data boundary. Only page-level components (`src/pages/**/*.vue`) define queries/mutations and call `useQuery`/`useMutation`. This prevents data fetching from being scattered throughout the component tree.

**Data components** define fragments to declare what fields they render. They receive data via props typed with their own fragment type.

**Pure UI components** (`VQInput`, `FormActions`, `TagList`, etc.) have no GraphQL dependency — props use plain TypeScript types.

## Defining a Fragment

Fragments go in a `<script lang="ts">` block, separate from `<script setup>`:

```vue
<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment avatarImage on User {
    email
  }
`)
</script>

<script setup lang="ts">
import type { avatarImageFragment } from "src/graphql/generated/graphql"

interface Props {
  user: avatarImageFragment
}
defineProps<Props>()
</script>
```

The `graphql()` call registers the fragment with codegen. The generated type (`avatarImageFragment`) is imported separately — this makes it clear the type is generated, not returned by the `graphql()` function.

### Why Two Script Blocks?

`<script setup>` cannot contain module-level declarations visible to external tooling. The `graphql()` call lives in `<script lang="ts">` so codegen can discover it when scanning `.vue` files. Both blocks share the same module scope.

## Composing Fragments

Components spread their children's fragments:

```vue
<!-- UserListItem.vue -->
<script lang="ts">
graphql(`
  fragment userListItem on User {
    id
    name
    username
    ...avatarImage
  }
`)
</script>
```

Fragment composition flows upward: leaf components define minimal fragments, parents spread them and add their own fields, and the page query spreads the top-level fragment.

## Defining Queries (Pages Only)

Pages define queries in `<script lang="ts">` and import the generated typed document in `<script setup>`:

```vue
<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetUsers($page: Int) {
    userSearch(page: $page) {
      data {
        ...userListBasic
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { useQuery } from "@vue/apollo-composable"
import { GetUsersDocument } from "src/graphql/generated/graphql"

const { result } = useQuery(GetUsersDocument, () => ({
  page: currentPage.value
}))
</script>
```

Importing `GetUsersDocument` (rather than using the `graphql()` return value) makes it explicit that the typed document is a generated artifact.

## Naming Conventions

| Convention | Example |
|---|---|
| Fragment name = component name (camelCase) | `AvatarImage.vue` → `fragment avatarImage` |
| Generated type appends `Fragment` | `avatarImage` → `avatarImageFragment` |
| Generated document appends `Document` | `query GetUsers` → `GetUsersDocument` |

## Type Guidelines

- **Prefer fragment types over schema types.** Use `userListItemFragment` instead of `User`. The full schema type means you're over-fetching or not declaring data needs.
- **Props use the component's own fragment type**, not a parent's or the full schema type.
- **Emit payloads** use fragment types when passing data objects up.
- **Pure UI components** use plain types — no GraphQL dependency.

## Centralized Operations (Legacy)

Some operations remain in `src/graphql/{queries,mutations,fragments}.ts` using `gql` template literals. These coexist with colocated operations. As components are migrated, the centralized files will shrink.

# Structuring queries for QueryTable

`QueryTable` is opinionated about the shape of the GraphQL query it
runs. It owns pagination, search, and sort variables, and reads the
paginator's `paginatorInfo` to drive page state. Your query is
responsible for declaring those variables and selecting the matching
fields.

This page covers everything you need to ship a working query —
required variables, the `QueryTable` fragment, the `field` prop, and
how cell renderers compose their own fragments into the selection.

## The minimum viable query

```graphql
query GetUsers(
  $page: Int
  $first: Int
  $search: String
  $orderBy: [QueryUsersOrderByOrderByClause!]
) {
  users(page: $page, first: $first, search: $search, orderBy: $orderBy) {
    ...QueryTable
    data {
      id
      name
      email
    }
  }
}
```

Three things are non-negotiable:

1. The operation declares `$page: Int` and `$first: Int`.
2. The paginator field spreads the `...QueryTable` fragment.
3. The paginator's `data` selection includes every field the columns
   or cell renderers consume.

`$search` and `$orderBy` are optional but enable extra behavior — see
[Optional variables](#optional-variables).

## Required variables

| Variable | Type  | Why                                                                                              |
| -------- | ----- | ------------------------------------------------------------------------------------------------ |
| `$page`  | `Int` | Drives the paginator's current page. Without it the search/sort/pagination plumbing is disabled. |
| `$first` | `Int` | Page size. Without it the per-page selector is hidden.                                           |

`QueryTable` reads the operation's variable definitions at runtime via
`useQueryCapabilities` to decide what UI to render:

- No `$page` → pagination footer is hidden.
- No `$first` → rows-per-page dropdown is hidden.
- No `$search` → search input is hidden.

So omit a variable only when you want the corresponding UI gone.

## Optional variables

### `$search: String`

Declare `$search` and pass it to the paginator field to enable the
top-bar search input. `QueryTable` debounces the input and pipes the
current value into the variable for you.

```graphql
users(page: $page, first: $first, search: $search) {
  ...QueryTable
  data { id name email }
}
```

### `$orderBy: [...OrderByClause!]`

Declare `$orderBy` to enable column sorting. `QueryTable` builds the
clause from the active sort column and direction:

```ts
[{ column: "NAME", order: "DESC" }]
```

The `column` value is `pagination.sortBy.toUpperCase()`. Lighthouse's
`@orderBy` directive generates a per-query enum (e.g.
`QueryUsersOrderByColumn`), so column names must match the enum
case-insensitively. Mark the column `sortable: true` in
[QueryTableColumn](./index.md#columns) to expose the sort affordance.

## The `QueryTable` fragment

`QueryTable.vue` defines and registers this fragment:

```graphql
fragment QueryTable on Paginator {
  paginatorInfo {
    count
    currentPage
    lastPage
    perPage
    total
  }
}
```

It spreads on any type implementing Lighthouse's `Paginator`
interface — i.e. the result of an `@paginate` field. The component
reads `paginatorInfo` to drive page count, total rows, and per-page
state. **Forgetting to spread the fragment is the most common
cause of "the table renders but pagination is wrong."**

## The `field` prop and nested paginators

`QueryTable` walks the query result to find the paginator. By default
it grabs the first key of the result object:

```graphql
query GetUsers(...) {
  users(...) { ...QueryTable; data { ... } }
}
```

```vue
<QueryTable :query="GetUsersDocument" ... />
```

For a paginator nested under a parent field, pass a dotted path to
the `field` prop:

```graphql
query getUserPublications($id: ID, $page: Int!, $first: Int!) {
  user(id: $id) {
    id
    publications(first: $first, page: $page) {
      ...QueryTable
      data {
        id
        role
        publication { id name }
      }
    }
  }
}
```

```vue
<QueryTable
  :query="getUserPublicationsDocument"
  field="user.publications"
  ...
/>
```

## Composing cell-renderer fragments

A cell renderer **may** publish its own GraphQL fragment that callers
spread into the paginator's `data` selection. When a renderer takes
this approach, its docs name the fragment.

Spreading a renderer-owned fragment keeps maintenance and reuse
easy: the renderer owns its data shape, callers don't have to
remember which fields belong to which cell, and adding a new field
to the renderer is a single-file change instead of a sweep across
every consuming page.

```graphql
query GetUsers(...) {
  users(...) {
    ...QueryTable
    data {
      id
      username
      email
      created_at
      ...NameAvatarCell
    }
  }
}
```

Spread additional fragments as needed — they compose freely with the
`...QueryTable` fragment on the paginator and any fragments on `data`.

## Extra variables via the `variables` prop

`page`, `first`, `search`, and `orderBy` are managed by `QueryTable`.
Everything else (filters, parent IDs, date ranges, etc.) is passed in
via the `variables` prop:

```vue
<QueryTable
  :query="getUserPublicationsDocument"
  :variables="{ id }"
  field="user.publications"
  ...
/>
```

`QueryTable` merges these on top of its managed pagination variables
on every refetch, so updating `variables` reactively re-runs the
query.

When a filter changes you typically also want to reset to page 1:

```ts
const tableRef = ref<InstanceType<typeof QueryTable>>()

watch(filters, () => {
  if (tableRef.value) tableRef.value.page = 1
})
```


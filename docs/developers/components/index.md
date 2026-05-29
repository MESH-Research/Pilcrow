# Components

This section documents conventions and configuration for shared,
project-specific Vue components that ship with Pilcrow's client.

These are the in-house wrappers and primitives that downstream pages
are expected to use directly. For framework primitives (Quasar's
`q-table`, `q-btn`, `q-breadcrumbs`, etc.), consult the
[Quasar documentation](https://quasar.dev/) instead.

## In this section

- [QueryTable](./query-table/index.md) — paginated, URL-synced
  GraphQL table built on `q-table`.
  - [Cell renderers](./query-table/cells.md) — built-in cells plus
    how to author your own.
  - [Structuring queries](./query-table/queries.md) — required
    variables, the `QueryTable` fragment, and nested paginators.
  - [Filters](./query-table/filters.md) — wiring page-level filter
    state through `variables` and the URL.
- [BreadCrumbs](./breadcrumbs.md) — route-driven breadcrumb trail
  built from `meta.crumb` declarations.

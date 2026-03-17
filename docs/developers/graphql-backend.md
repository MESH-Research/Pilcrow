# Backend GraphQL

The backend API is a [Laravel](https://laravel.com) application with [Lighthouse](https://lighthouse-php.com/) providing GraphQL server functionality.

## Schema Definition

The schema is defined across multiple `.graphql` files in `backend/graphql/`. The entry point is `backend/graphql/schema.graphql`, which composes the schema definition using Lighthouse's `#import` directive.

## Compiled Schema

To view the compiled schema as the client sees it:

```sh
lando artisan lighthouse:print-schema
```

To export the compiled schema to a file (used by the client's [code generation](./graphql-client#typescript-code-generation) for offline/CI workflows):

```sh
lando artisan lighthouse:print-schema > client/src/graphql/schema.graphql
```

::: tip
The client also provides `lando yarn graphql:fetch-schema` as a shortcut for this command. During normal development, the schema file is updated automatically by the dev server when backend schema changes are detected — see [Automatic Regeneration](./graphql-client#automatic-regeneration-dev-server).
:::

## GraphQL Playground

When running in development mode, Lighthouse provides a GraphQL playground at <https://pilcrow.lndo.site/graphiql> for testing queries and mutations interactively.

## Further Reading

- [Lighthouse documentation](https://lighthouse-php.com/master/getting-started/installation.html)
- [Laravel documentation](https://laravel.com/docs)

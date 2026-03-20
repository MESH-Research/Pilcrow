# Client GraphQL

The client application uses [Apollo Client](https://www.apollographql.com/docs/react/) to communicate with the backend GraphQL API. All operations are defined centrally, and TypeScript types are generated automatically from the backend schema.

## Operations

All GraphQL operations are defined centrally:

| File | Contents |
|------|----------|
| `client/src/graphql/fragments.ts` | Reusable fragments (`currentUserFields`, `relatedUserFields`, etc.) |
| `client/src/graphql/queries.ts` | All queries (`CurrentUser`, `GetSubmission`, etc.) |
| `client/src/graphql/mutations.ts` | All mutations (`Login`, `CreateSubmissionDraft`, etc.) |

Operations are defined using `graphql-tag`'s `gql` template literals and exported as constants (e.g., `CURRENT_USER`, `LOGIN`). Fragments are interpolated into operations using template literal expressions.

## TypeScript Code Generation

We use [`@graphql-codegen`](https://the-guild.dev/graphql/codegen) to generate TypeScript types from the backend schema and the client's operation files. This provides typed interfaces for query results, mutation responses, and variables without changing existing `useQuery`/`useMutation` call sites.

### Generated Output

Codegen produces `client/src/graphql/generated/graphql.ts` containing:

- **Schema types**: `User`, `Submission`, `Publication`, enums like `SubmissionStatus`, input types, etc.
- **Operation types**: `CurrentUserQuery`, `LoginMutation`, `CreateSubmissionDraftMutationVariables`, etc.
- **Fragment types**: `currentUserFieldsFragment`, `relatedUserFieldsFragment`, etc.

This file is gitignored — it's a derived artifact regenerated from the schema and operation files.

### How Generated Types Are Named

The generated type name is derived from the **operation name** inside the `gql`
document, not from the JavaScript constant:

```typescript
// The constant name doesn't matter for codegen — the operation name does:
export const CURRENT_USER = gql`
  query CurrentUser {        // ← generates CurrentUserQuery
    currentUser { ... }
  }
`

export const LOGIN = gql`
  mutation Login($input: LoginInput!) {  // ← generates LoginMutation
    login(input: $input) { ... }           //    and LoginMutationVariables
  }
`
```

Codegen appends `Query` or `Mutation` to the operation name to produce the
result type, and adds `Variables` for any operation that accepts arguments.

::: warning Operation names must be unique
Codegen generates types in a single file, so every operation name across all
query and mutation files must be unique. If two operations share a name, codegen
will silently merge or overwrite their types.
:::

### How It Works

The codegen config is in `client/codegen.ts`. By default, it introspects the running backend at `http://pilcrow.lndo.site/graphql` to get the compiled schema, then generates types for all operations found in `client/src/graphql/**/*.ts`.

### Automatic Regeneration (Dev Server)

During development, types are regenerated automatically when:

- **Dev server starts**: Introspects the backend and generates types
- **Client operations change**: Editing `queries.ts`, `mutations.ts`, or `fragments.ts` triggers regeneration
- **Backend schema changes**: Editing `.graphql` files in `backend/graphql/` triggers re-introspection and regeneration
- **Build**: Types are generated from the committed `schema.graphql` file (no backend needed)

If the backend is not running when the dev server starts, `throwOnStart: false` allows the dev server to start anyway using previously generated types.

::: tip
The compiled schema file (`client/src/graphql/schema.graphql`) is committed to the repository. During development, codegen introspects the live backend and writes both the types and an updated schema file automatically. During builds, types are generated from this committed schema file so the backend is not required. If you need to update the schema file manually (e.g., without the dev server running), use `lando yarn graphql:fetch-schema`.
:::

### Manual Commands

Run these from the project root using `lando yarn`:

```sh
# Run codegen using introspection (requires lando running)
lando yarn graphql:codegen

# Export schema then generate types from the file (offline/CI)
lando yarn graphql:codegen-offline

# Export compiled schema only
lando yarn graphql:fetch-schema
```

You can override the schema source with an environment variable. This is useful for offline or CI workflows where introspection isn't available:

```sh
lando ssh -s client -c "GRAPHQL_SCHEMA=src/graphql/schema.graphql npm run graphql:codegen"
```

### Using Generated Types

See [TypeScript Conventions](./typescript) for patterns on using generated types
in components and tests, including typed mock responses, derived types, and
boundary casting.

### Configuration

The codegen configuration in `client/codegen.ts` uses:

- `schema-ast` plugin — writes the introspected schema to `schema.graphql` (keeps the committed file in sync)
- `typescript` plugin — generates base types from the schema
- `typescript-operations` plugin — generates result/variable types for each operation
- `namingConvention: "keep"` — preserves snake_case field names from the Laravel backend
- `maybeValue: "T | null | undefined"` — nullable fields can be `null` or `undefined`

## Further Reading

- [GraphQL Codegen documentation](https://the-guild.dev/graphql/codegen/docs/getting-started)
- [Apollo Client Vue integration](https://v4.apollo.vuejs.org/)

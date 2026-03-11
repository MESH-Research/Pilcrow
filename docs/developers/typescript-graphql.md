# Using GraphQL Generated Types

This is where typed TypeScript provides the most value for regression
prevention. Every test that provides mock GraphQL data should type that data
against the generated query/mutation types.

## Typing Mock Responses

Wrap mock handler responses in the generated query type:

```typescript
import type { GetSubmissionQuery } from "src/graphql/generated/graphql"
import { SubmissionStatus } from "src/graphql/generated/graphql"

const mockResponse: { data: GetSubmissionQuery } = {
  data: {
    submission: {
      __typename: "Submission",
      id: "1",
      title: "Test Submission",
      status: SubmissionStatus.INITIALLY_SUBMITTED,
      // ... all fields required by the query
    }
  }
}
handler.mockResolvedValue(mockResponse)
```

**What this catches:** If the backend renames `title` to `name`, or adds a
required field, or changes the `status` enum values, the test fails at compile
time -- not silently at runtime with wrong data.

## Deriving Types from Queries

When you need the type of a nested object (e.g., a single submission from a
list), derive it from the query type rather than writing a parallel interface:

```typescript
import type { CurrentUserSubmissionsQuery } from "src/graphql/generated/graphql"

type SubmissionData = NonNullable<
  CurrentUserSubmissionsQuery["currentUser"]
>["submissions"][number]
```

`NonNullable<>` strips the `null | undefined` from nullable fields (codegen
generates `Maybe<T>` which is `T | null | undefined`). The `[number]` index
extracts the element type from an array.

This keeps test helper types automatically in sync with the schema -- no
manual updates needed when fields change.

## Using Generated Enums

Always use the generated enum values instead of magic strings:

```typescript
import { SubmissionStatus } from "src/graphql/generated/graphql"

// ✅ Compile error if the enum value is renamed or removed
mockSubmission("100", SubmissionStatus.UNDER_REVIEW, "submitter")

// ❌ Silent breakage if the backend changes the value
mockSubmission("100", "UNDER_REVIEW", "submitter")
```

## Boundary Casting for Partial Mocks

Tests often don't need every field of a large generated type. Cast at the
boundary (the object literal) with a specific type, not `as any`:

```typescript
// ✅ TypeScript still checks the fields you provide
const props = {
  container: {
    __typename: "Publication",
    id: "1",
    editors: [
      { id: "1", email: "test@example.com", name: "TestUser" } as User,
    ]
  } as Publication
}

// ❌ No type checking at all
const props = {
  container: { id: "1", editors: [...] } as any
}
```

`as Publication` tells TypeScript "trust me, this satisfies `Publication`"
while still checking the fields you *do* provide against the type. `as any`
disables all checking entirely.

::: tip When to use `as any`
`as any` is a last resort. The one common case where it's unavoidable is
`wrapper.vm as any` when accessing `<script setup>` internals from test-utils,
because Vue does not expose those types. Always prefer a narrower cast first.
:::

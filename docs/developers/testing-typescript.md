# Bugs Caught by Convention

These are latent bugs that already existed in the JavaScript code and were only
discovered during the TypeScript migration. None of them produced visible errors
in JS -- they silently did the wrong thing.

These bugs were caught because the codebase follows specific TypeScript
conventions. Each example includes a callout linking to the convention that
makes the catch possible.

### Negating a Ref Object Instead of Its Value

In `InlineComment.vue`, the untyped `inject("forExport")` returned a
`Ref<boolean>`, but the code treated it as a plain boolean:

```typescript
// ❌ Bug: !forExport negates the Ref object (always truthy), so isCollapsed
//    was always initialized to false regardless of the actual forExport value
const isCollapsed = ref(!forExport)

// ✅ Fix: access .value to get the actual boolean
const isCollapsed = ref(!forExport.value)
```

The same component also had `if (forExport) return false` in a computed -- a
Ref object is always truthy, so the reply button was always hidden during
export. TypeScript caught this once `forExport` had a known type of
`Ref<boolean>`, because `!forExport` is a boolean negation of an object.

::: tip Convention: Type-Safe Provide/Inject
Using `InjectionKey<T>` gives `inject()` a known return type. Without it,
`inject("forExport")` returns `unknown` and this bug stays hidden. See
[Type-Safe Provide/Inject](./typescript-conventions#type-safe-provide-inject).
:::

### Test Mocks with Wrong Types

Across many test files, mock data had values that didn't match the GraphQL
schema types. These all passed silently in JavaScript:

```typescript
// ❌ Bug: GraphQL ID fields are strings, not numbers
{ id: 1, status: 0 }

// ✅ Fix: correct types match what the API actually returns
{ id: "1", status: SubmissionStatus.INITIALLY_SUBMITTED }
```

The `status: 0` case is especially notable -- the component's `watchEffect`
compared `submission.status` against string enum values like
`"INITIALLY_SUBMITTED"`. With `status: 0` (an integer), none of the
comparisons matched, so the test was silently not exercising the view-switching
logic it was supposed to test.

::: tip Convention: Typed Mock Responses
Annotating mock data as `{ data: GetSubmissionQuery }` forces every field to
match the generated type. See
[Typing Mock Responses](./typescript-graphql#typing-mock-responses).
:::

### Test Provides with Wrong Types

Test provide blocks passed plain booleans where Refs were expected:

```typescript
// ❌ Bug: injection type is Ref<boolean>, but test provided a plain boolean
global: { provide: { commentDrawerOpen: true } }

// ✅ Fix: provide the correct Ref type
global: { provide: { [commentDrawerOpenKey]: ref(true) } }
```

::: tip Convention: Type-Safe Provide/Inject
Using the typed `InjectionKey` symbol as the provide key (instead of a plain
string) means TypeScript checks that the provided value matches the expected
type. See
[Type-Safe Provide/Inject](./typescript-conventions#type-safe-provide-inject).
:::

### Wrong Mutation Operation Names

In `src/graphql/mutations.ts`, three mutations had operation names that didn't
match their actual purpose:

```typescript
// ❌ Bug: constant is for submitters, but GQL operation said "ReviewCoordinators"
export const UPDATE_SUBMISSION_SUBMITERS = gql`
  mutation UpdateSubmissionReviewCoordinators(...) { ... }
`
// ❌ Bug: constant is for creating a comment, but GQL operation said "Reply"
export const CREATE_INLINE_COMMENT = gql`
  mutation CreateInlineCommentReply(...) { ... }
`
```

These went unnoticed in JS because the operation name is just a string inside
the document. With codegen, the generated TypeScript types are keyed on these
operation names, so the mismatch immediately surfaced.

::: tip Convention: Generated Type Naming
Codegen derives type names from the operation name, not the JS constant. A
mismatch means the generated type doesn't match what the code expects. See
[How Generated Types Are Named](./graphql-client#how-generated-types-are-named).
:::

### Wrong Field Name on a GraphQL Type

In `CommentEditor.vue`, style criteria items were rendered with a field that
doesn't exist on the `StyleCriteria` type:

```vue
<!-- ❌ Bug: StyleCriteria has "name", not "label" — rendered blank -->
<q-expansion-item :label="criteria.label" />

<!-- ✅ Fix: use the actual field name -->
<q-expansion-item :label="criteria.name" />
```

::: tip Convention: Use Generated Types for Data
Importing generated GraphQL types for component props and data means
TypeScript catches field name mismatches at compile time. See
[Props and Emits](./typescript-conventions#props-and-emits).
:::

## Everyday TypeScript Catches

These bugs don't depend on any project-specific convention -- they're the kind
of mistakes that TypeScript catches out of the box through basic type checking.

### Destructuring a Non-Existent Property

In `src/use/submission.ts`, `useMutation` returns `{ mutate, loading, ... }`
but the code destructured a property that doesn't exist:

```typescript
// ❌ Bug: useMutation has no "saving" property — this silently assigned undefined
const { mutate, saving } = useMutation(CREATE_SUBMISSION_DRAFT)

// ✅ Fix: rename the actual "loading" property
const { mutate, loading: saving } = useMutation(CREATE_SUBMISSION_DRAFT)
```

Any loading spinner or disabled state that depended on `saving` never worked --
it was always `undefined` (falsy).

### Calling `.value` on a Plain Array

In `src/use/user.ts`, the `isApplicationAdmin` function built a plain array
with `.map()` but then accessed `.value` on it as if it were a Ref:

```typescript
// ❌ Bug: roles is a plain string[], not a Ref — .value is undefined
const roles = user.roles.map(({ name }) => name) ?? []
return !!roles.value.includes("Application Administrator")

// ✅ Fix: access the array directly
const roles = user.roles.map(({ name }) => name) ?? []
return !!roles.includes("Application Administrator")
```

This would throw `Cannot read properties of undefined` at runtime. TypeScript
caught it because `string[]` has no `.value` property.

### Wrong Config Keys (Silent No-Op)

In `CommentEditor.vue`, TipTap's `StarterKit.configure()` received lowercase
keys that don't match the registered extension names:

```typescript
// ❌ Bug: wrong keys — config silently ignored, extensions left at defaults
StarterKit.configure({
  codeblock: false,
  hardbreak: false,
  horizontalrule: false,
})

// ✅ Fix: correct camelCase keys
StarterKit.configure({
  codeBlock: false,
  hardBreak: false,
  horizontalRule: false,
})
```

### Bitwise OR Instead of Logical OR

In `AssignedSubmissionUsers.vue`:

```typescript
// ❌ Bug: | is bitwise OR — coerces booleans to 0/1, wrong precedence with &&
(props.maxUsers === false) | (users.value.length < props.maxUsers) && ...

// ✅ Fix: || is logical OR
(props.maxUsers === false || users.value.length < props.maxUsers) && ...
```

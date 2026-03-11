# TypeScript Conventions

## Component Conventions

### Script Setup

All components use `<script setup lang="ts">`.

### Props and Emits

Always declare `defineProps` and `defineEmits` using **named interfaces**, not
inline type literals. This is enforced by the
`vue/define-emits-declaration` and `vue/define-props-declaration` ESLint rules.

```vue
<script setup lang="ts">
import type { Publication } from "src/graphql/generated/graphql"

interface Props {
  publication: Publication
  mutable?: boolean
}

interface Emits {
  update: [publication: Publication]
  delete: []
}

const props = withDefaults(defineProps<Props>(), {
  mutable: false,
})
const emit = defineEmits<Emits>()
</script>
```

Key points:

- Import generated GraphQL types for prop types (`Publication`, `User`,
  `Submission`, etc.) rather than defining parallel interfaces.
- Use `withDefaults` for optional props with default values.
- Emit payloads use tuple syntax: `eventName: [arg1: Type, arg2: Type]`.

## Type-Safe Provide/Inject

Vue's `provide`/`inject` is untyped by default -- `inject("key")` returns
`unknown` and there is no compile-time check that the provider and consumer
agree on the value type. We use `InjectionKey<T>` to make this type-safe.

The pattern is centralized in composable files like `src/use/submissionContext.ts`:

```typescript
import type { InjectionKey, Ref, ComputedRef } from "vue"
import type { Submission } from "src/graphql/generated/graphql"

// Typed key -- binds the symbol to its value type
export const submissionKey: InjectionKey<ComputedRef<Submission | undefined>> =
  Symbol("submission")

// Typed accessor -- consumers get the correct type automatically
export function useSubmission() {
  return inject(submissionKey)!
}

// Centralized provider -- one function provides all related context
export function provideSubmissionReviewContext(options: {
  submission: ComputedRef<Submission | undefined>
  activeComment?: Ref<ActiveComment | null>
}) {
  provide(submissionKey, options.submission)
  provide(activeCommentKey, options.activeComment ?? ref(null))
}
```

This pattern prevents three classes of bugs:

1. **Type mismatches** -- providing a `Ref<string>` where a
   `ComputedRef<Submission>` is expected is a compile error.
2. **Forgotten provides** -- grouping all related provides in one function
   makes it harder to forget one.
3. **Wrong key** -- using a typed `InjectionKey` instead of a plain string means
   typos are caught at compile time.

## Utility Type Patterns

### Deriving Form Types from GraphQL Types

The `FormStrings<T>` mapped type transforms a generated GraphQL type into a
form-friendly shape by stripping `__typename`, removing optionality, and
unwrapping nullables:

```typescript
type FormStrings<T> = {
  [K in keyof T as K extends "__typename" ? never : K]-?: NonNullable<T[K]>
}

// Usage
type SocialMediaFields = FormStrings<SocialMedia>
// Result: { twitter: string; instagram: string; ... }
```

### Structural Interfaces for Composables

Composables that accept Apollo query/mutation results use structural interfaces
rather than importing Apollo's exact types. This makes them easier to test with
plain objects:

```typescript
interface MutationLike {
  loading: Ref<boolean>
  error?: Ref<Error | null>
}

export function useFormState(query: QueryLike | null, mutation: MutationLike) {
  // ...
}
```

### Vuelidate Validator Type

Vuelidate's built-in `BaseValidation` type is too complex for Vue's prop
system. A custom structural interface captures only the fields our components
actually use:

```typescript
// src/types/vuelidate.ts
export interface VuelidateValidator {
  $model: unknown
  $path: string
  $error: boolean
  $errors: ErrorObject[]
  $dirty: boolean
  $touch: () => void
  $reset: () => void
}
```

## Known Limitations

A few areas still use `as any` or ESLint suppressions. These are tracked with
context and suggested fixes in `client/REFACTOR.md`:

- **`wrapper.vm as any`** -- Vue test-utils does not expose `<script setup>`
  internals through TypeScript.
- **Vuelidate dynamic state** -- 6 locations access Vuelidate properties
  (e.g., `$response.complexity`) beyond what our `VuelidateValidator` models.
- **Dialog runtime emits** -- Quasar's `useDialogPluginComponent` requires the
  runtime array form of `defineEmits`, preventing type-based declarations.

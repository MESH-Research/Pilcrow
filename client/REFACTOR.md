# Client Refactor TODO

- [ ] [Make `MutationLike.error` required in `useFormState`](#make-mutationlikeerror-required-in-useformstate)
- [ ] [Have `useVQWrap` delegate to `useI18nPrefix` internally](#have-usevqwrap-delegate-to-usei18nprefix-internally)
- [ ] [Derive `ProfileMetadataFields` from GraphQL fragments](#derive-profilemetadatafields-from-graphql-fragments)
- [ ] [Address `as any` casts for Vuelidate validators](#address-as-any-casts-for-vuelidate-validators)
- [ ] [Convert dialog runtime emits to type-based declarations](#convert-dialog-runtime-emits-to-type-based-declarations)
- [ ] [Address `@typescript-eslint/no-explicit-any` suppressions](#address-typescript-eslintno-explicit-any-suppressions)
- [ ] [Type `extractElement` in `usePagination`](#type-extractelement-in-usepagination)
- [ ] [Create test mock factories for GraphQL types](#create-test-mock-factories-for-graphql-types)

---

## Make `MutationLike.error` required in `useFormState`

In `src/use/forms/formState.ts`, the `MutationLike` interface has `error` as optional (`error?`), but Apollo's `UseMutationReturn` always provides `error: Ref<ApolloError | null>` — it exists but may be null, not absent. The property was made optional as a workaround because some callers pass hand-built objects without `error`.

**Change:** Make `error` required and update callers to pass `error: ref(null)` explicitly.

**Files:**
- `src/use/forms/formState.ts` — change `error?` to `error`, remove `?? ref(null)` fallback
- `src/components/PublicationStyleCriteria.vue` — add `error: ref(null)`
- `src/components/atoms/VQInput.vitest.spec.ts`
- `src/use/forms/formState.vitest.spec.ts`
- `src/components/forms/StyleCriteriaForm.vitest.spec.ts`
- `src/components/forms/ProfileMetadataForm.vitest.spec.ts`
- `src/components/forms/AccountProfileForm.vitest.spec.ts`
- `src/components/molecules/FormActions.vitest.spec.ts`

## Have `useVQWrap` delegate to `useI18nPrefix` internally

`src/use/forms/vQWrap.ts` reimplements the same prefix + `t()` pattern that `src/use/i18nPrefix.ts` (`useI18nPrefix`) already provides. The `tPrefix` computed, `getTranslationKey()`, and `getTranslation()` in `useVQWrap` could be replaced by calling `useI18nPrefix(tPrefix)` and using its `pt()` function.

**Current code in `vQWrap.ts`:**
```typescript
const tPrefix = computed(() => {
  if (typeof tPath === "string") return tPath
  const prefix = parentTPrefix ? `${parentTPrefix}.` : ""
  return `${prefix}${validator.$path}`
})

function getTranslationKey(key: string) {
  return `${tPrefix.value}.${key}`
}

function getTranslation(key: string) {
  return t(getTranslationKey(key))
}
```

**Could become:**
```typescript
const tPrefix = computed(() => {
  if (typeof tPath === "string") return tPath
  const prefix = parentTPrefix ? `${parentTPrefix}.` : ""
  return `${prefix}${validator.$path}`
})

const { pt: getTranslation, prefixKey: getTranslationKey } = useI18nPrefix(tPrefix)
```

**Note:** `useI18nPrefix` currently doesn't expose a `prefixKey()` function (only `pt`/`pte`/`t`/`te`), so it would need a small addition to return the raw key builder. Consumers of `useVQWrap` that call `getTranslationKey()` (e.g. `VQInput.vue`, `ErrorFieldRenderer` prefix prop) need the unprefixed key string, not just the translated result.

**Files:**
- `src/use/i18nPrefix.ts` — expose `prefixKey()` helper
- `src/use/forms/vQWrap.ts` — replace manual prefix logic with `useI18nPrefix`

## Derive `ProfileMetadataFields` from GraphQL fragments

`SocialMediaFields` and `AcademicProfileFields` in `src/use/profileMetadata.ts` are now derived from GraphQL generated types using `FormStrings<T>`. However, `ProfileMetadataFields` is still manually defined because the generated `ProfileMetadata` type has nested object fields (`social_media`, `academic_profiles`) and `websites` typed as `Maybe<Array<Maybe<string>>>` — making a simple mapped type insufficient.

**Change:** Define a GraphQL fragment for the profile metadata fields used in forms, then derive `ProfileMetadataFields` from the fragment's generated type. This would keep the form type fully in sync with the query/mutation shape.

**Files:**
- `src/graphql/fragments.ts` — add a `ProfileMetadataFormFields` fragment
- `src/use/profileMetadata.ts` — derive `ProfileMetadataFields` from the fragment type

## Address `as any` casts for Vuelidate validators

6 `as any` casts exist, mostly to access Vuelidate validation state that lacks proper type definitions:

- `src/pages/AcceptInvite.vue:56` — `($v.password as any).notComplex?.$response?.complexity`
- `src/pages/ResetPassword.vue:33` — same pattern
- `src/pages/SubmissionDraft.vue:76` — `(draft.content as any).required?.$invalid`
- `src/components/molecules/InlineComments.vue:15` — `:comment="comment as any"`
- `src/components/SubmissionTitle.vue:102` — `newPubV$.value.title as any`
- `src/components/forms/ProfileMetadataForm.vue:206` — `(v$.value as any).profile_metadata`

**Approach:** These likely need a custom type declaration or wrapper for Vuelidate's dynamic validation object shape. Consider creating a `VuelidateValidation<T>` utility type or augmenting `@vuelidate/core` types.

## Convert dialog runtime emits to type-based declarations

6 dialog components use runtime `defineEmits([...useDialogPluginComponent.emits])` with an `eslint-disable vue/define-emits-declaration` suppression:

- `src/components/dialogs/DiscardChangesDialog.vue`
- `src/components/dialogs/SelectIconDialog.vue`
- `src/components/dialogs/ConfirmStatusChangeDialog.vue`
- `src/components/dialogs/BypassStyleCriteriaDialog.vue`
- `src/components/dialogs/ReinviteUserDialog.vue`
- `src/components/dialogs/ConfirmCommentDeletion.vue`

**Approach:** Quasar's `useDialogPluginComponent` emits `ok`, `hide`, and `cancel`. These could be converted to a shared `interface DialogEmits` and then extended per-dialog if needed. However, `useDialogPluginComponent` requires the runtime array form — investigate whether Quasar provides a type-only alternative or if a wrapper is needed.

## Address `@typescript-eslint/no-explicit-any` suppressions

3 files suppress the `no-explicit-any` rule:

- `src/use/pagination.ts:67` — `Record<string, any>` in `extractElement()` — see [dedicated section](#type-extractelement-in-usepagination)
- `src/components/SubmissionTitle.vue:101` — Vuelidate validator cast (see Vuelidate section above)
- `src/components/forms/ProfileMetadataForm.vue:205` — Vuelidate validator cast (see Vuelidate section above)

## Type `extractElement` in `usePagination`

`extractElement()` in `src/use/pagination.ts` uses `Record<string, any>` because it dynamically indexes into a GraphQL query result. The `element` parameter only ever receives two literal values: `"data"` and `"paginatorInfo"`.

**Current code:**
```typescript
// eslint-disable-next-line @typescript-eslint/no-explicit-any
function extractElement(data: Record<string, any>, element: string): unknown {
  const keys = Object.keys(data)
  if (keys.length !== 1) {
    throw "Unable to extract query return (Are you sure this is a paginated query?)"
  }
  return data[keys[0]][element]
}
```

**Change:** Narrow `element` to a union type and type `data` to match the paginated query shape:

```typescript
interface PaginatedQueryResult {
  [queryName: string]: {
    data: unknown[]
    paginatorInfo: PaginatorInfo
  }
}

function extractElement<K extends "data" | "paginatorInfo">(
  data: PaginatedQueryResult,
  element: K
): PaginatedQueryResult[string][K] {
  const keys = Object.keys(data)
  if (keys.length !== 1) {
    throw "Unable to extract query return (Are you sure this is a paginated query?)"
  }
  return data[keys[0]][element]
}
```

This removes the `any` and the eslint suppression, and gives callers proper return types (`unknown[]` for `"data"`, `PaginatorInfo` for `"paginatorInfo"`).

**Further improvement:** `usePagination` already accepts a `doc: DocumentNode` parameter. With `@graphql-typed-document-node/core` (already used by codegen), the `doc` parameter can carry the query's result type. This would let `extractElement` infer the return shape directly from the document type instead of using a hand-written `PaginatedQueryResult` interface — and would also eliminate the `as T[]` / `as PaginatorInfo` casts at the call sites.

**Files:**
- `src/use/pagination.ts` — refactor `extractElement` signature, remove eslint suppression

## Create test mock factories for GraphQL types

Test files pass partial mock objects for generated GraphQL types (e.g. `User`, `Submission`, `Publication`). These types have many required fields that tests don't care about, leading to either `as any` casts or type errors on `mount()`/`setProps()` calls.

**Change:** Create factory helpers in `test/vitest/` that produce properly typed mock objects with sensible defaults, overridable per-test.

**Example:**
```typescript
// test/vitest/factories.ts
import type { User, Submission } from "src/graphql/generated/graphql"

export function mockUser(overrides: Partial<User> = {}): User {
  return {
    __typename: "User",
    id: "1",
    email: "test@example.com",
    username: "testuser",
    name: "Test User",
    created_at: "2024-01-01T00:00:00Z",
    roles: [],
    submissions: [],
    notifications: { __typename: "NotificationPaginator", data: [], paginatorInfo: { ... } },
    ...overrides
  }
}
```

**Files needing factory usage (tests with partial GraphQL mock data):**
- `src/components/AssignedSubmissionUsers.vitest.spec.ts` — partial `User` in `reviewers` arrays, partial `Submission` containers
- `src/components/AssignedPublicationUsers.vitest.spec.ts` — partial `User` in `editors` arrays, partial `Publication` containers
- `src/components/forms/AccountProfileForm.vitest.spec.ts` — `accountProfile: {} as any`
- `src/use/forms/graphQLValidation.vitest.spec.ts` — `error.value = {} as any` (needs `ApolloError` factory)
- `src/components/PublicationStyleCriteria.vitest.spec.ts` — `(form().at(0) as any).vm.$emit(...)`
- `src/components/forms/StyleCriteriaForm.vitest.spec.ts` — `(wrapper.emitted("delete")![0][0] as any).id`

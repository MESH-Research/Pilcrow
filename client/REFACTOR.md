# Client Refactor TODO

- [ ] [Make `MutationLike.error` required in `useFormState`](#make-mutationlikeerror-required-in-useformstate)
- [ ] [Have `useVQWrap` delegate to `useI18nPrefix` internally](#have-usevqwrap-delegate-to-usei18nprefix-internally)
- [ ] [Derive `ProfileMetadataFields` from GraphQL fragments](#derive-profilemetadatafields-from-graphql-fragments)
- [ ] [Convert remaining Vue SFCs to `<script setup lang="ts">`](#convert-remaining-vue-sfcs-to-script-setup-langts)
- [ ] [Address `as any` casts for Vuelidate validators](#address-as-any-casts-for-vuelidate-validators)
- [ ] [Convert dialog runtime emits to type-based declarations](#convert-dialog-runtime-emits-to-type-based-declarations)
- [ ] [Convert test files from JS to TS](#convert-test-files-from-js-to-ts)
- [ ] [Address `@typescript-eslint/no-explicit-any` suppressions](#address-typescript-eslintno-explicit-any-suppressions)

---

## Make `MutationLike.error` required in `useFormState`

In `src/use/forms/formState.ts`, the `MutationLike` interface has `error` as optional (`error?`), but Apollo's `UseMutationReturn` always provides `error: Ref<ApolloError | null>` — it exists but may be null, not absent. The property was made optional as a workaround because some callers pass hand-built objects without `error`.

**Change:** Make `error` required and update callers to pass `error: ref(null)` explicitly.

**Files:**
- `src/use/forms/formState.ts` — change `error?` to `error`, remove `?? ref(null)` fallback
- `src/components/PublicationStyleCriteria.vue` — add `error: ref(null)`
- `src/components/atoms/VQInput.vitest.spec.js`
- `src/use/forms/formState.vitest.spec.js`
- `src/components/forms/StyleCriteriaForm.vitest.spec.js`
- `src/components/forms/ProfileMetadataForm.vitest.spec.js`
- `src/components/forms/AccountProfileForm.vitest.spec.js`
- `src/components/molecules/FormActions.vitest.spec.js`

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

## Convert remaining Vue SFCs to `<script setup lang="ts">`

~1 Vue SFC still lacks `lang="ts"` on its script block (down from ~61):

- `src/components/atoms/CommentActions.vue`

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

## Convert test files from JS to TS

All 48 test files are `.vitest.spec.js` and should be renamed to `.vitest.spec.ts` with proper type annotations added. This is a large but mechanical task. Key directories:

- `src/components/` (most test files)
- `src/use/` (composable tests)
- `src/apollo/` (router guard tests)

## Address `@typescript-eslint/no-explicit-any` suppressions

3 files suppress the `no-explicit-any` rule:

- `src/use/pagination.ts:67` — `Record<string, any>` in `extractElement()` — needs a generic or proper type
- `src/components/SubmissionTitle.vue:101` — Vuelidate validator cast (see Vuelidate section above)
- `src/components/forms/ProfileMetadataForm.vue:205` — Vuelidate validator cast (see Vuelidate section above)

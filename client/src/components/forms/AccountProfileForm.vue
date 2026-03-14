<template>
  <q-form data-cy="vueAccount" @submit="onSubmit">
    <v-q-wrap t-prefix="account.account.fields" @vqupdate="updateVQ">
      <form-section first-section>
        <template #header>{{ $t(`account.profile.update_email`) }}</template>
        <v-q-input ref="emailInput" :v="v$.email" data-cy="update_user_email" />
      </form-section>
      <form-section>
        <template #header>{{ $t(`account.profile.update_password`) }}</template>
        <VNewPasswordInput
          ref="passwordInput"
          data-cy="update_user_password"
          :v="v$.password"
        >
        </VNewPasswordInput>
      </form-section>
      <form-actions @reset-click="onRevert" />
    </v-q-wrap>
  </q-form>
</template>

<script setup lang="ts">
import FormSection from "src/components/molecules/FormSection.vue"
import FormActions from "src/components/molecules/FormActions.vue"
import VQInput from "src/components/atoms/VQInput.vue"
import VQWrap from "src/components/atoms/VQWrap.vue"
import VNewPasswordInput from "./VNewPasswordInput.vue"
import { useVuelidate } from "@vuelidate/core"
import { reactive, watchEffect, watch, inject, computed, ref } from "vue"
import { isEqual, pick } from "lodash"
import { updateUserRules as rules } from "src/use/userValidation"
import type { VuelidateValidator } from "src/types/vuelidate"
import type { ValidationErrors } from "src/use/forms/graphQLValidation"
import type { User } from "src/graphql/generated/graphql"

interface Props {
  accountProfile: Pick<User, "username" | "name" | "email"> | null | undefined
  graphqlValidation?: ValidationErrors
}

const props = withDefaults(defineProps<Props>(), {
  graphqlValidation: () => ({})
})

const $externalResults = ref<ValidationErrors>({})

watch(
  () => props.graphqlValidation,
  (newValue) => {
    $externalResults.value = (newValue.user as ValidationErrors) ?? {}
  }
)

interface Emits {
  save: [form: { password: string; email: string }]
}

const emit = defineEmits<Emits>()

const original = computed(() => ({
  password: "",
  ...pick(props.accountProfile, ["username", "name", "email"])
}))

const form = reactive({
  password: "",
  email: ""
})

delete rules.password.required

const v$ = useVuelidate(rules, form, {
  $externalResults
})

import { formStateKey } from "src/use/forms"
const { dirty, errorMessage } = inject(formStateKey)

watchEffect(() => {
  dirty.value = !isEqual(original.value, form)
})

watchEffect(() => {
  if (!props.accountProfile) return
  Object.assign(form, original.value)
})

function updateVQ(validator: VuelidateValidator, newValue: unknown) {
  validator.$model = newValue
}

function onRevert() {
  Object.assign(form, original.value)
  v$.value.$clearExternalResults()
  errorMessage.value = ""
}
function onSubmit() {
  v$.value.$touch()
  if (v$.value.$invalid) {
    errorMessage.value = "Oops, check form about for errors."
  } else {
    emit("save", form)
  }
}
</script>

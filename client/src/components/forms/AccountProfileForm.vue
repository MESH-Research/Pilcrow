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

<script setup>
import FormSection from "src/components/molecules/FormSection.vue"
import FormActions from "src/components/molecules/FormActions.vue"
import VQInput from "src/components/atoms/VQInput.vue"
import VQWrap from "src/components/atoms/VQWrap.vue"
import VNewPasswordInput from "./VNewPasswordInput.vue"
import { useVuelidate } from "@vuelidate/core"
import { reactive, watchEffect, watch, inject, computed, ref } from "vue"
import { isEqual, pick } from "lodash"
import { updateUserRules as rules } from "src/use/userValidation"
const props = defineProps({
  accountProfile: {
    required: true,
    validator: (v) =>
      v === null || typeof v === "object" || typeof v === "undefined",
  },
  graphqlValidation: {
    required: false,
    type: Object,
    default: () => ({}),
  },
})
const $externalResults = ref({})

watch(
  () => props.graphqlValidation,
  (newValue) => {
    $externalResults.value = newValue.user ?? []
  },
)

const emit = defineEmits(["save"])

const original = computed(() => ({
  password: "",
  ...pick(props.accountProfile, ["username", "name", "email"]),
}))

const form = reactive({
  password: "",
  email: "",
})

delete rules.password.required

const v$ = useVuelidate(rules, form, {
  $externalResults,
})

const { dirty, errorMessage } = inject("formState")

watchEffect(() => {
  dirty.value = !isEqual(original.value, form)
})

watchEffect(() => {
  if (!props.accountProfile) return
  Object.assign(form, original.value)
})

function updateVQ(validator, newValue) {
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

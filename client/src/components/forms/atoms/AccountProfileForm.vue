<template>
  <q-form data-cy="vueAccount" @submit="onSubmit">
    <v-q-wrap t-prefix="account.account.fields" @vqupdate="updateVQ">
      <form-section first-section>
        <template #header>Account Information</template>
        <v-q-input ref="nameInput" :v="v$.name" data-cy="update_user_name" />
        <v-q-input ref="emailInput" :v="v$.email" data-cy="update_user_email" />
        <v-q-input
          ref="usernameInput"
          :v="v$.username"
          data-cy="update_user_username"
        />
      </form-section>
      <form-section>
        <template #header>Update Password</template>
        <NewPasswordInput
          ref="passwordInput"
          v-model="v$.password.$model"
          outlined
          data-cy="update_user_password"
          :complexity="v$.password.notComplex.$response.complexity"
          :error="v$.password.$error"
          label="Password"
        >
          <template #append>
            <q-icon class="cursor-pointer" />
          </template>
          <template #error>
            <error-field-renderer
              :errors="v$.password.$errors"
              prefix="auth.validation.password"
            />
          </template>
        </NewPasswordInput>
      </form-section>
      <form-actions @reset-click="onRevert" />
    </v-q-wrap>
  </q-form>
</template>

<script setup>
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
import FormSection from "src/components/molecules/FormSection.vue"
import FormActions from "src/components/molecules/FormActions.vue"
import VQInput from "src/components/atoms/VQInput.vue"
import VQWrap from "src/components/atoms/VQWrap.vue"
import NewPasswordInput from "../NewPasswordInput.vue"
import { useVuelidate } from "@vuelidate/core"
import { reactive, watchEffect, inject, computed } from "vue"
import { isEqual, pick } from "lodash"
import { rules } from "src/use/userValidation"
const props = defineProps({
  accountProfile: {
    type: Object,
    required: true,
  },
})

const emit = defineEmits(["save"])

const original = computed(() => ({
  password: "",
  ...pick(props.accountProfile, ["username", "name", "email"]),
}))

const form = reactive({
  username: "",
  password: "",
  email: "",
  name: "",
})

delete rules.password.required

const v$ = useVuelidate(rules, form)

const { dirty } = inject("formState")

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
}
function onSubmit() {
  emit("save", form)
}
</script>

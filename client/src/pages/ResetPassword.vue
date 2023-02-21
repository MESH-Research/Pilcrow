<template>
  <q-page class="flex-center flex q-pa-md" data-cy="reset_page">
    <q-card style="width: 400px" square>
      <q-form @submit="handleSubmit()">
        <q-card-section class="accent q-py-xs">
          <h1 class="text-h4 text-white">Reset Password</h1>
        </q-card-section>
        <q-card-section class="q-pa-lg">
          <fieldset class="q-px-sm q-pt-md q-gutter-y-lg q-pb-lg">
            <p>Enter your email address to request a password reset.</p>
            <q-input
              ref="username"
              v-model="v$.email.$model"
              :error="v$.email.$error"
              :label="$t('auth.fields.email')"
              autofocus
              outlined
              data-cy="email_field"
              autocomplete="username"
            >
              <template #error>
                <error-field-renderer
                  :errors="v$.email.$errors"
                  prefix="auth.validation.email"
                />
              </template>
            </q-input>
          </fieldset>
        </q-card-section>
      </q-form>
    </q-card>
  </q-page>
</template>

<script setup>
import { reactive } from "vue"
import { useVuelidate } from "@vuelidate/core"
import { required, email } from "@vuelidate/validators"
const credentials = reactive({
  email: "",
})
const rules = {
  email: {
    email,
    required,
  },
}
const v$ = useVuelidate(rules, credentials)

const handleSubmit = function () {
  console.log(`Hello`)
}
</script>

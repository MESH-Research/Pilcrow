<template>
  <q-page v-if="status === 'submitted'" class="column flex-center">
    <q-icon color="positive" name="check_circle" size="2em" />
    <strong class="text-h3">{{ $t(`reset_password.submitted.title`) }}</strong>
    <p>{{ $t(`reset_password.submitted.byline`) }}</p>
  </q-page>
  <q-page v-else class="flex-center flex q-pa-md" data-cy="reset_page">
    <q-card style="width: 400px" square>
      <q-form @submit="handleSubmit()">
        <q-card-section class="accent q-py-xs">
          <h1 class="text-h4 text-white">
            {{ $t(`reset_password.title`) }}
          </h1>
        </q-card-section>
        <q-card-section>
          <fieldset class="q-px-sm q-pt-md q-gutter-y-lg q-pb-lg">
            <p>{{ $t(`reset_password.byline`) }}</p>
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
        <q-card-actions>
          <q-btn
            id="submitBtn"
            ref="submitBtn"
            type="submit"
            :label="$t(`guiElements.form.submit`)"
            unelevated
            color="accent"
            class="full-width text-white"
            :loading="status === 'submitting'"
          />
          <q-btn
            :label="$t(`guiElements.form.cancel`)"
            flat
            stretch
            class="q-mt-md full-width text-white"
            to="/login"
          />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-page>
</template>

<script setup>
import { reactive, ref } from "vue"
import { useVuelidate } from "@vuelidate/core"
import { required, email } from "@vuelidate/validators"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer"
const status = ref("")
const address = reactive({
  email: "",
})
const rules = {
  email: {
    email,
    required,
  },
}
const v$ = useVuelidate(rules, address)

const handleSubmit = function () {
  if (v$.value.email.$error) {
    return
  }
  status.value = "submitting"
  setTimeout(() => {
    status.value = "submitted"
  }, 1000)
}
</script>

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
            {{ $t(`reset_password.request.title`) }}
          </h1>
        </q-card-section>
        <q-card-section>
          <fieldset class="q-px-sm q-pt-md q-gutter-y-lg q-pb-lg">
            <p>{{ $t(`reset_password.request.byline`) }}</p>
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
          <ul v-if="status === 'error'" class="text-negative">
            <li v-for="(message, index) in errorMessagesList" :key="index">
              {{ message }}
            </li>
          </ul>
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
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
import { REQUEST_PASSWORD_RESET } from "src/graphql/mutations"
import { email, required } from "@vuelidate/validators"
import { reactive, ref } from "vue"
import { useMutation } from "@vue/apollo-composable"
import { useVuelidate } from "@vuelidate/core"
import { useGraphErrors } from "src/use/errors"

const errorMessagesList = ref([])
const address = reactive({
  email: "",
})
const { mutate: request } = useMutation(REQUEST_PASSWORD_RESET)
const status = ref("")
const rules = {
  email: {
    email,
    required,
  },
}
const { errorMessages, graphQLErrorCodes } = useGraphErrors()
const v$ = useVuelidate(rules, address)
const handleSubmit = async function () {
  if (v$.value.email.$error) {
    return
  }
  try {
    status.value = "submitting"
    await request({
      email: address.email,
    })
    status.value = "submitted"
  } catch (error) {
    errorMessagesList.value = errorMessages(
      graphQLErrorCodes(error),
      "auth.validation.email"
    )
    status.value = "error"
  }
}
</script>

<template>
  <q-page v-if="status === 'updated'" class="column flex-center">
    <q-icon color="positive" name="check_circle" size="2em" />
    <strong class="text-h3">{{ $t(`reset_password.updated.title`) }}</strong>
    <p>{{ $t(`reset_password.updated.byline`) }}</p>
    <q-btn
      :label="$t('reset_password.updated.action')"
      class="q-mt-lg"
      color="accent"
      icon="arrow_forward"
      size="md"
      to="/dashboard"
    />
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
            <new-password-input
              ref="passwordInput"
              v-model="$v.password.$model"
              outlined
              :label="$t('auth.fields.password')"
              :error="$v.password.$error"
              :complexity="$v.password.notComplex.$response.complexity"
              data-cy="password_field"
            >
              <template #error>
                <error-field-renderer
                  :errors="$v.password.$errors"
                  prefix="auth.validation.password"
                />
              </template>
            </new-password-input>
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
            :loading="status === 'updating'"
          />
        </q-card-actions>
      </q-form>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import NewPasswordInput from "src/components/forms/NewPasswordInput.vue"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
import { RESET_PASSWORD } from "src/graphql/mutations"
import { CURRENT_USER } from "src/graphql/queries"
import { useGraphErrors } from "src/use/errors"
import { useUserValidation } from "src/use/userValidation"

definePage({
  name: "ResetPassword"
})

const {
  params: { token },
  query
} = useRoute("ResetPassword")
const errorMessagesList = ref([])
const { mutate: request } = useMutation(RESET_PASSWORD, {
  refetchQueries: [{ query: CURRENT_USER }]
})
const status = ref("")
const { errorMessages, graphQLErrorCodes } = useGraphErrors()
const { $v } = useUserValidation()
const handleSubmit = async function () {
  if ($v.value.password.$error || $v.value.password.$model == "") {
    return
  }
  try {
    status.value = "updating"
    await request({
      email: query.email,
      password: $v.value.password.$model,
      token: token
    })
    status.value = "updated"
  } catch (error) {
    errorMessagesList.value = errorMessages(
      graphQLErrorCodes(error),
      "reset_password"
    )
    status.value = "error"
  }
}
</script>

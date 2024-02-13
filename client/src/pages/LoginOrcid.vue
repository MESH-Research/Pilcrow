<template>
  <q-page>
    <section class="q-pa-lg">
      <div v-if="errorMessage" class="column flex-center">
        <div style="max-width: 400px">
          <error-banner>
            {{ errorMessage }}
          </error-banner>
        </div>
      </div>
      <div v-if="status == 'loading'" class="column flex-center">
        <q-spinner color="primary" size="2em" />
        <strong class="text-h3">{{ $t("loading") }}</strong>
      </div>
      <div v-else-if="action == 'register'" class="column flex-center">
        <q-form style="width: 400px" @submit="handleRegister">
          <h1>{{ $t("auth.oauth.register.title") }}</h1>
          <fieldset class="q-pb-lg q-px-none q-gutter-y-lg column">
            <q-input
              ref="nameInput"
              v-model.trim="$v.name.$model"
              outlined
              :label="$t('helpers.OPTIONAL_FIELD', [$t('auth.fields.name')])"
              autocomplete="name"
              data-cy="name_field"
              bottom-slots
            />
            <q-input
              ref="usernameInput"
              v-model.trim="$v.username.$model"
              outlined
              :label="$t('auth.fields.username')"
              :error="$v.username.$error"
              autocomplete="nickname"
              debounce="500"
              data-cy="username_field"
              bottom-slots
            >
              <template #error>
                <error-field-renderer
                  :errors="$v.username.$errors"
                  prefix="auth.validation.username"
                />
              </template>
            </q-input>
            <q-input
              ref="emailInput"
              v-model.trim="$v.email.$model"
              outlined
              type="email"
              :label="$t('auth.fields.email')"
              :error="$v.email.$error"
              data-cy="email_field"
              bottom-slots
            >
              <template #error>
                <error-field-renderer
                  :errors="$v.email.$errors"
                  prefix="auth.validation.email"
                />
              </template>
            </q-input>
            <error-banner v-if="form_error">
              {{ $t(`auth.failures.${form_error}`) }}
            </error-banner>
          </fieldset>

          <q-card-actions class="q-py-lg q-px-none">
            <q-btn
              unelevated
              size="lg"
              color="accent"
              class="full-width text-white"
              :label="$t('auth.oauth.submit.btn_label')"
              type="submit"
            />
          </q-card-actions>
        </q-form>
      </div>
    </section>
  </q-page>
</template>

<script setup>
import { ref, onMounted, watch } from "vue"
import { useRoute, useRouter } from "vue-router"
import { useMutation, useQuery } from "@vue/apollo-composable"
import { useUserValidation } from "src/use/userValidation"
import { CURRENT_USER } from "src/graphql/queries"
import {
  LOGIN_ORCID_CALLBACK,
  REGISTER_OAUTH_USER,
} from "src/graphql/mutations"
import ErrorBanner from "src/components/molecules/ErrorBanner.vue"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"

const { push } = useRouter()
const route = useRoute()
const code = route.query.code
const { mutate: handleCallback } = useMutation(LOGIN_ORCID_CALLBACK, {
  variables: { code: code },
})
const { mutate: registerOauthUser } = useMutation(REGISTER_OAUTH_USER)

const id = ref(null)
let form_error = ref(null)
let action = ref(null)
let status = ref("loading")
let errorMessage = ref(null)
const provider = ref(null)
const { $v, user } = useUserValidation({
  mutation: registerOauthUser,
  rules: (rules) => {
    delete rules.password.required
  },
  variables: (form) => {
    return { id, ...form }
  },
})
const { result, error, refetch } = useQuery(CURRENT_USER, {
  fetchPolicy: "network-only",
})

function handleRedirect() {
  const pollInterval = setInterval(() => {
    refetch()
  }, 500)

  watch(status, () => {
    if (status.value == "error") {
      clearInterval(pollInterval)
    }
  })
  watch(result, () => {
    clearInterval(pollInterval)
    push({ path: "/dashboard/" })
  })
  watch(error, (errorData) => {
    if (errorData) {
      status.value = "error"
      errorMessage.value = errorData
      console.error("Error in redirect", errorData)
      clearInterval(pollInterval)
    }
  })
}
function logError(error, message = "") {
  status.value = "error"
  errorMessage.value = error
  console.error(message, error)
}

onMounted(async () => {
  try {
    await handleCallback()
      .then((response) => {
        const data = response.data.loginOrcidCallback
        action.value = data.action
        provider.value = data.provider
        Object.assign(user, data.user)
        status.value = "loaded"
      })
      .then((e) => {
        logError(e)
        if (action.value == "auth") {
          handleRedirect()
        }
      })
      .catch((error) => {
        logError(error, "Error in callback catch")
      })
  } catch (error) {
    logError(error, "Error in callback try catch")
  }
})

async function handleRegister() {
  try {
    errorMessage.value = ""
    form_error.value = ""
    status.value = "loading"
    await registerOauthUser({
      user_name: $v.value.name.$model,
      user_username: $v.value.username.$model,
      user_email: $v.value.email.$model,
      provider_name: provider.value.provider_name,
      provider_id: provider.value.provider_id,
    })
      .then(handleRedirect())
      .catch((error) => {
        form_error.value = error.message
        logError(error, "Error in register catch")
      })
  } catch (e) {
    form_error.value = e.message
    logError(e, "Error in register try catch")
  }
}
</script>

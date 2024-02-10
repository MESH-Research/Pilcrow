<template>
  <q-page>
    <section class="q-pa-lg">
      <div v-if="status == 'loading'" class="column flex-center">
        <q-spinner color="primary" size="2em" />
        <strong class="text-h3">{{ $t("loading") }}</strong>
      </div>
      <div v-if="status == 'error'" class="column flex-center">
        <error-banner>
          {{ errorMessage }}
        </error-banner>
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

function handleRedirect() {
  const { result, error, refetch } = useQuery(CURRENT_USER, {
    fetchPolicy: "network-only",
  })
  const pollInterval = setInterval(() => {
    refetch()
  }, 500)

  watch(result, (resultData) => {
    console.log("Polled Data", resultData)
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

onMounted(async () => {
  try {
    await handleCallback()
      .then((response) => {
        const data = response.data.loginOrcidCallback
        action.value = data.action
        provider.value = data.provider
        Object.assign(user, data.user)
      })
      .then(handleRedirect())
      .catch((error) => {
        console.log("Error in callback catch")
        console.error(error)
      })
  } catch (error) {
    console.log("Error in try catch")
    console.error(error)
  }
})

async function handleRegister() {
  try {
    console.log("Hello World")
    form_error.value = ""
    status.value = "loading"
    const a = await registerOauthUser({
      user_name: $v.value.name.$model,
      user_username: $v.value.username.$model,
      user_email: $v.value.email.$model,
      provider_name: provider.value.provider_name,
      provider_id: provider.value.provider_id,
    })
    console.log("a", a)
    push({ path: "/dashboard/" })
    console.log("b")
  } catch (e) {
    status.value = "error"
    form_error.value = e.message
    console.error(e)
  }
}
</script>

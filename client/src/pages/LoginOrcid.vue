<template>
  <q-page>
    <section class="q-pa-lg">
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
              :error="$v.name.$error"
              data-cy="name_field"
              bottom-slots
            >
              <template #error>
                <error-field-renderer
                  :errors="$v.name.$errors"
                  prefix="auth.validation.name"
                />
              </template>
            </q-input>
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

                <i18n-t
                  v-if="hasErrorKey('email', 'EMAIL_IN_USE')"
                  keypath="auth.validation.email.EMAIL_IN_USE_HINT"
                  tag="div"
                  style="line-height: 1.3"
                >
                  <template #loginAction>
                    <router-link to="/login">
                      {{ $t("auth.login_help") }}
                    </router-link>
                  </template>
                  <template #passwordAction>
                    <router-link to="/reset-password">
                      {{ $t("auth.password_help") }}
                    </router-link>
                  </template>
                  <template #break>
                    <br />
                  </template>
                </i18n-t>
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
              :disable="status == 'submitting'"
            />
          </q-card-actions>
        </q-form>
      </div>
      <div v-if="status == 'submitting'" class="column flex-center">
        <q-spinner color="primary" size="2em" />
      </div>
    </section>
  </q-page>
</template>

<script setup>
import ErrorBanner from "src/components/molecules/ErrorBanner.vue"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
import { applyExternalValidationErrors } from "src/use/validationHelpers"
import { ref, onMounted, watch, reactive } from "vue"
import { useHasErrorKey } from "src/use/validationHelpers"
import { useMutation, useQuery } from "@vue/apollo-composable"
import { useRoute, useRouter } from "vue-router"
import { useUserValidation } from "src/use/userValidation"
import { CURRENT_USER } from "src/graphql/queries"
import {
  LOGIN_OAUTH_CALLBACK,
  REGISTER_OAUTH_USER,
} from "src/graphql/mutations"

const { push } = useRouter()
const route = useRoute()
const code = route.query.code
const { mutate: handleCallback } = useMutation(LOGIN_OAUTH_CALLBACK, {
  variables: { provider_name: "orcid", code: code },
})
const { mutate: registerOauthUser } = useMutation(REGISTER_OAUTH_USER)

let form_error = ref(null)
let action = ref(null)
let status = ref("loading")
const provider = ref(null)
const { $v, user } = useUserValidation({
  mutation: registerOauthUser,
  rules: (rules) => {
    delete rules.password.required
  },
})
const hasErrorKey = useHasErrorKey($v)
const { result, error, refetch } = useQuery(CURRENT_USER, {
  fetchPolicy: "network-only",
})

function handleRedirect() {
  const pollInterval = setInterval(() => {
    refetch()
  }, 1000)

  watch(result, () => {
    clearInterval(pollInterval)
    push({ path: "/dashboard/" })
  })
  watch(status, () => {
    if (status.value == "error") {
      clearInterval(pollInterval)
    }
  })
  watch(error, (errorData) => {
    if (errorData) {
      clearInterval(pollInterval)
      handleError("INTERNAL")
    }
  })
}

onMounted(async () => {
  try {
    await handleCallback()
      .then((response) => {
        const data = response.data.loginOauthCallback
        action.value = data.action
        provider.value = data.provider
        Object.assign(user, data.user)
        status.value = "loaded"
      })
      .then(() => {
        if (action.value == "auth") {
          handleRedirect()
        }
      })
  } catch (e) {
    handleError("INTERNAL")
  }
})

const externalValidation = reactive({
  name: [],
  username: [],
  email: [],
})

async function handleRegister() {
  try {
    $v.value.$touch()
    if ($v.value.$invalid || $v.value.$error) {
      handleError("FORM_VALIDATION")
    }
    status.value = "submitting"
    form_error.value = ""
    await registerOauthUser({
      user_name: $v.value.name.$model,
      user_username: $v.value.username.$model,
      user_email: $v.value.email.$model,
      provider_name: provider.value.provider_name,
      provider_id: provider.value.provider_id,
    }).then(handleRedirect())
  } catch (e) {
    if (applyExternalValidationErrors(user, externalValidation, e, "user.")) {
      handleError("FORM_VALIDATION")
    } else {
      handleError("INTERNAL")
    }
  }
}

function handleError(message) {
  status.value = "error"
  form_error.value = message
}
</script>

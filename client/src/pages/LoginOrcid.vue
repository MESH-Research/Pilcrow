<template>
  <q-page>
    <section class="q-pa-lg">
      <div v-if="status == 'loading'" class="column flex-center">
        <q-spinner color="primary" size="2em" />
        <strong class="text-h3">{{ $t("loading") }}</strong>
      </div>
      <div v-else-if="action == 'register'" class="column flex-center">
        <q-form style="width: 400px" @submit="handleSubmit">
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
              v-model.trim="email"
              outlined
              type="email"
              :label="$t('auth.fields.email')"
              bottom-slots
              data-cy="email_field"
            />
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
      <div v-else-if="action == 'auth'">
        <p>Time to auth</p>
      </div>
      <div v-else>
        <p>Whoops</p>
      </div>
    </section>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from "vue"
import { useRoute, useRouter } from "vue-router"
import { useMutation } from "@vue/apollo-composable"
import { useUserValidation } from "src/use/userValidation"
import {
  LOGIN_ORCID_CALLBACK,
  CREATE_EXTERNAL_IDENTITY_PROVIDER_ID,
} from "src/graphql/mutations"
import ErrorBanner from "src/components/molecules/ErrorBanner.vue"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"

const { push } = useRouter()
const route = useRoute()
const code = route.query.code
const { mutate: handleCallback } = useMutation(LOGIN_ORCID_CALLBACK, {
  variables: { code: code },
})
const { mutate: createIdentityProvider } = useMutation(
  CREATE_EXTERNAL_IDENTITY_PROVIDER_ID
)
let form_error = ref(null)
let action = ref(null)
const id = ref(null)
const email = ref("")
let provider_id = ref(null)
let provider_name = ref(null)
// const { mutate: finish_oauth } = useMutation(ACCEPT_SUBMISSION_INVITE)
let status = ref("loading")
const { $v, user, saveUser } = useUserValidation({
  // mutation: finish_oauth,
  rules: (rules) => {
    delete rules.email
  },
  variables: (form) => {
    return { id, ...form }
  },
})

onMounted(async () => {
  try {
    const response = await handleCallback()
    const data = response.data.loginOrcidCallback
    action.value = data.action
    Object.assign(user, data.user)
    status.value = 'loaded'
    console.log(data)
  } catch (error) {
    console.error(error)
  }
})

async function handleSubmit() {
  form_error.value = ""
  try {
    status.value = "loading"
    const user = await saveUser()
    //
    if (action.value == 'register') {
      await createIdentityProvider({
        provider_name: provider_id.value,
        provider_id: provider_name.value,
        user_id: user.id,
      })
    }
    // authenticate user
    // await loginUser({ email: user.email, password: user.password })

    push("/dashboard")

  } catch (e) {
    form_error.value = e.message
  }
}
</script>

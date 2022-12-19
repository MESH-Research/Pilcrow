<template>
  <q-page>
    <section class="q-pa-lg">
      <div v-if="status == 'loading'" class="column flex-center">
        <q-spinner color="primary" size="2em" />
        <strong class="text-h3">{{ $t("loading") }}</strong>
      </div>
      <div v-if="status == 'verified'" class="column flex-center">
        <q-form style="width: 400px" @submit="handleSubmit">
          <h1>{{ $t("submissions.accept_invite.update_details.title") }}</h1>
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
              disable
              type="email"
              :label="$t('auth.fields.email')"
              autocomplete="username"
              debounce="500"
              bottom-slots
              data-cy="email_field"
            />
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
            <error-banner v-if="form_error">
              {{ $t(form_error) }}
            </error-banner>
          </fieldset>

          <q-card-actions class="q-py-lg q-px-none">
            <q-btn
              unelevated
              size="lg"
              color="accent"
              class="full-width text-white"
              :label="
                $t('submissions.accept_invite.update_details.update_action')
              "
              type="submit"
            />
          </q-card-actions>
        </q-form>
      </div>
      <div v-if="status == 'accepted'" class="column flex-center">
        <q-icon color="positive" name="check_circle" size="2em" />
        <strong class="text-h3">{{
          $t("submissions.accept_invite.update_details.success.title")
        }}</strong>
        <p>
          {{ $t("submissions.accept_invite.update_details.success.message") }}
        </p>
        <q-btn
          class="q-mr-sm"
          color="accent"
          size="md"
          :label="$t('auth.login')"
          to="/login"
        />
      </div>
      <div v-if="status == 'update_error'" class="column flex-center">
        <q-icon color="negative" name="error" size="2em" />
        <strong class="text-h3">{{
          $t("submissions.accept_invite.update_details.failure.title")
        }}</strong>
        <p>
          {{ $t("submissions.accept_invite.update_details.failure.message") }}
        </p>
      </div>
      <div v-if="status == 'verification_error'" class="column flex-center">
        <q-icon color="negative" name="error" size="2em" />
        <strong class="text-h3">{{
          $t("submissions.accept_invite.update_details.failure.title")
        }}</strong>
        <p>
          {{ $t(`submissions.accept_invite.verify.${verification_error}`) }}
        </p>
      </div>
    </section>
  </q-page>
</template>

<script setup>
import NewPasswordInput from "../components/forms/NewPasswordInput.vue"
import {
  VERIFY_SUBMISSION_INVITE,
  ACCEPT_SUBMISSION_INVITE,
} from "src/graphql/mutations"
import { ref, onMounted } from "vue"
import { useMutation } from "@vue/apollo-composable"
import { useRoute } from "vue-router"
import { useUserValidation } from "src/use/userValidation"
import ErrorBanner from "src/components/molecules/ErrorBanner.vue"
// import { useLogin } from "src/use/user"
// const { loginUser } = useLogin()
const { $v, user } = useUserValidation()

const status = ref("loading")

const { mutate: verify } = useMutation(VERIFY_SUBMISSION_INVITE)
const { mutate: accept } = useMutation(ACCEPT_SUBMISSION_INVITE)
const { params } = useRoute()
let form_error = ref(null)
let verification_error = ref(null)

onMounted(async () => {
  const { uuid, expires, token } = params

  try {
    const response = await verify({ uuid, expires, token })
    Object.assign(user, response.data.verifySubmissionInvite)
    status.value = "verified"
  } catch (error) {
    verification_error.value = error.graphQLErrors[0].extensions.code
    status.value = "verification_error"
  }
})
async function handleSubmit() {
  const { uuid, expires, token } = params
  if (!user.password) {
    form_error.value = "auth.validation.password.required"
    return
  }

  try {
    let name = user.name
    let username = user.username
    let password = user.password
    await accept({ uuid, expires, token, name, username, password })
    // await loginUser({ email: user.email, password: user.password })
    status.value = "accepted"
  } catch (e) {
    console.log(user, e)
    status.value = "update_error"
  }
}
</script>

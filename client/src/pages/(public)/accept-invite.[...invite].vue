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
              v-model.trim="email"
              outlined
              disable
              type="email"
              :label="$t('auth.fields.email')"
              bottom-slots
              data-cy="email_field"
            />
            <new-password-input
              ref="passwordInput"
              v-model="$v.password.$model"
              outlined
              :label="$t('auth.fields.password')"
              :error="$v.password.$error"
              :complexity="$v.password.notComplex?.$response.complexity"
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
              {{ $t(`auth.failures.${form_error}`) }}
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
          :label="$t('submissions.accept_invite.update_details.success.action')"
          :to="{
            name: 'submission:details',
            params: { id: cta_id }
          }"
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
          $t("submissions.accept_invite.verify.failure.title")
        }}</strong>
        <p>
          {{
            $t(`submissions.accept_invite.verify.failure.${verification_error}`)
          }}
        </p>
      </div>
    </section>
  </q-page>
</template>

<script setup lang="ts">
import NewPasswordInput from "src/components/forms/NewPasswordInput.vue"
import ErrorBanner from "src/components/molecules/ErrorBanner.vue"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"

import { useUserValidation } from "src/use/userValidation"
import { useLogin } from "src/use/user"
import {
  AcceptSubmissionInviteDocument,
  VerifySubmissionInviteDocument
} from "src/gql/graphql"

definePage({
  name: "acceptInvite"
})
const { loginUser } = useLogin()
const { params } = useRoute("acceptInvite")
const { mutate: verify } = useMutation(VerifySubmissionInviteDocument)
const { mutate: accept } = useMutation(AcceptSubmissionInviteDocument)

const status = ref("loading")
const id = ref(null)
const cta_id = ref(null)
const email = ref("")

const form_error = ref(null)
const verification_error = ref(null)

const { $v, user, saveUser } = useUserValidation({
  mutation: accept,
  rules: (rules) => {
    delete rules.email
  },
  variables: (form) => {
    return { id, ...form, ...params }
  }
})
const [uuid, expires, token, submission_id] = params.invite.split("/")
onMounted(async () => {
  try {
    const response = await verify({ uuid, expires, token })
    const data = response.data.verifySubmissionInvite
    Object.assign(user, data)
    email.value = user.email
    id.value = data.id
    cta_id.value = submission_id
    status.value = "verified"
  } catch (error) {
    verification_error.value = error.graphQLErrors[0].extensions.code
    status.value = "verification_error"
  }
})

async function handleSubmit() {
  form_error.value = ""
  try {
    await saveUser()
    status.value = "loading"
    await loginUser({ email: user.email, password: user.password })
    status.value = "accepted"
  } catch (e) {
    form_error.value = e.message
  }
}
</script>

<script lang="ts">
graphql(`
  mutation VerifySubmissionInvite(
    $uuid: String!
    $token: String!
    $expires: String!
  ) {
    verifySubmissionInvite(uuid: $uuid, token: $token, expires: $expires) {
      id
      name
      email
      username
    }
  }
`)
graphql(`
  mutation AcceptSubmissionInvite(
    $uuid: String!
    $token: String!
    $expires: String!
    $id: ID!
    $name: String
    $username: String!
    $password: String!
  ) {
    acceptSubmissionInvite(
      uuid: $uuid
      token: $token
      expires: $expires
      user: { id: $id, name: $name, username: $username, password: $password }
    ) {
      id
      name
      email
      username
    }
  }
`)
</script>

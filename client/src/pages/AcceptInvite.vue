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
          <q-input
            ref="nameInput"
            outlined
            :label="$t('helpers.OPTIONAL_FIELD', [$t('auth.fields.name')])"
            autocomplete="name"
            data-cy="name_field"
            bottom-slots
          />
          <q-input
            ref="usernameInput"
            model-value=""
            outlined
            :label="$t('auth.fields.username')"
            autocomplete="nickname"
            debounce="500"
            data-cy="username_field"
            bottom-slots
          />
          <q-input
            ref="emailInput"
            outlined
            disable
            model-value=""
            type="email"
            :label="$t('auth.fields.email')"
            autocomplete="username"
            debounce="500"
            bottom-slots
            data-cy="email_field"
          />
          <new-password-input
            ref="passwordInput"
            outlined
            :label="$t('auth.fields.password')"
            :complexity="$v.password.notComplex.$response.complexity"
            data-cy="password_field"
          />
          <q-card-actions class="q-py-lg">
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
const { $v } = useUserValidation()

const status = ref("loading")
const submission_id = ref(null)

const { mutate: verify } = useMutation(VERIFY_SUBMISSION_INVITE)
const { mutate: accept } = useMutation(ACCEPT_SUBMISSION_INVITE)
const { params } = useRoute()

onMounted(async () => {
  const { uuid, expires, token } = params

  try {
    const result = await verify({ uuid, expires, token })
    submission_id.value = result.data.verifySubmissionInvite.id
    status.value = "verified"
  } catch (error) {
    status.value = "error"
  }
})
async function handleSubmit() {
  const { uuid, expires, token } = params
  try {
    const result = await accept({ uuid, expires, token })
    submission_id.value = result.data.verifySubmissionInvite.id
    status.value = "accepted"
  } catch (error) {
    status.value = "error"
  }
}
</script>

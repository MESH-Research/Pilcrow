<template>
  <q-page class="verify-page">
    <section class="q-pa-lg">
      <div v-if="status == 'loading'" class="column flex-center">
        <q-spinner color="primary" size="2em" />
        <strong class="text-h3">{{ $t("loading") }}</strong>
      </div>
      <div v-if="status === 'success'" class="column flex-center">
        <q-card rounded class="bg-primary q-pa-md q-ma-md">
          <q-icon color="white" name="email" size="2em" />
        </q-card>
        <p>
          {{ $t("account.email_verify.verification_success") }}
        </p>
        <q-btn
          class="q-mr-sm"
          color="accent"
          icon="arrow_forward"
          size="md"
          :label="$t('buttons.dashboard')"
          :to="{
            name: 'dashboard',
            params: { id: cta_id },
          }"
        />
      </div>
      <div v-if="status == 'failure'" class="column flex-center">
        <q-card rounded class="bg-negative q-pa-md q-ma-md">
          <q-icon color="white" name="error" size="2em" />
        </q-card>
        <p>
          {{ $t("general_failure") }}
        </p>
        <ul class="q-mb-xl">
          <li v-for="(message, index) in errorMessagesList" :key="index">
            {{ message }}
          </li>
        </ul>
        <email-verification-send-button color="accent" text-color="white" />
      </div>
    </section>
  </q-page>
</template>

<script setup>
import { VERIFY_EMAIL } from "src/graphql/mutations"
import EmailVerificationSendButton from "src/components/atoms/EmailVerificationSendButton.vue"
import { ref, onMounted } from "vue"
import { useCurrentUser } from "src/use/user"
import { useMutation } from "@vue/apollo-composable"
import { useRoute } from "vue-router"
import { useGraphErrors } from "src/use/errors"
const status = ref("loading")
const errorMessagesList = ref([])
const { currentUser } = useCurrentUser()

const { mutate: verifyEmail } = useMutation(VERIFY_EMAIL, {
  refetchQueries: ["currentUser"],
})
const { params } = useRoute()
const { errorMessages, graphQLErrorCodes } = useGraphErrors()
onMounted(async () => {
  const { token, expires } = params

  await currentUser
  if (currentUser.value.email_verified_at) {
    status.value = "success"
    return
  }
  try {
    await verifyEmail({ token, expires })
    status.value = "success"
  } catch (error) {
    errorMessagesList.value = errorMessages(
      graphQLErrorCodes(error),
      "account.failures",
    )
    status.value = "failure"
  }
})
</script>

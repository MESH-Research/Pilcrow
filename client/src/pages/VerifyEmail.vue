<template>
  <q-page class="verify-page">
    <q-card style="width: 600px">
      <q-card-section horizontal>
        <q-card-section
          class="card-icon flex-center flex"
          :class="status == 'failure' ? 'error' : ''"
        >
          <q-spinner-hourglass v-if="status == 'loading'" size="3em" />
          <q-icon v-else-if="status == 'success'" name="email" />
          <q-icon v-else name="error" />
        </q-card-section>
        <q-card-section class="col-grow q-pb-none">
          <div v-if="status == 'loading'">Loading...</div>
          <div v-else>
            <div v-if="status == 'success'">
              {{ $t("account.email_verify.verification_success") }}
            </div>
            <div v-else>
              {{ $t("general_failure") }}
              <ul class="errors">
                <li v-for="(message, index) in errorMessagesList" :key="index">
                  {{ message }}
                </li>
              </ul>
            </div>
          </div>
          <q-card-actions align="right">
            <email-verification-send-button
              v-if="status == 'failure'"
              flat
              no-color
            />
            <q-btn flat to="/dashboard">
              <q-icon name="arrow_forward" />
              {{ $t("buttons.dashboard") }}
            </q-btn>
          </q-card-actions>
        </q-card-section>
      </q-card-section>
    </q-card>
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
      "account.failures"
    )
    status.value = "failure"
  }
})
</script>

<style lang="scss">
.verify-page {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding-top: 3em;

  .card-icon {
    &.error {
      background-color: $red;
    }
    background-color: $primary;
    color: white;
    .q-icon {
      font-size: 3em;
    }
  }
}
</style>

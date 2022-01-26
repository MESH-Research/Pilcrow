<template>
  <q-btn
    :loading="status == 'loading'"
    :color="btnColor"
    class="text-black"
    v-bind="{ ...$props, ...$attrs }"
    @click="send"
  >
    <template v-if="status == 'success'">
      <q-icon name="check" />
      {{ $t("account.email_verify.resend_button_success") }}
    </template>
    <template v-else>
      {{ $t("account.email_verify.resend_button") }}
    </template>
    <template #loading>
      <q-spinner-hourglass class="on-left" />
      {{ $t("account.email_verify.resend_button_loading") }}
    </template>
  </q-btn>
</template>

<script setup>
import { SEND_VERIFY_EMAIL } from "src/graphql/mutations"
import { ref, computed } from "vue"
import { useMutation } from "@vue/apollo-composable"
import { useQuasar } from "quasar"
import { useGraphErrors } from "src/use/errors"
import { useI18n } from "vue-i18n"

const status = ref(null)
const btnColor = computed(() => {
  if (status.value == "success") {
    return "positive"
  }
  return null
})

const { mutate: sendEmail } = useMutation(SEND_VERIFY_EMAIL)
const { notify } = useQuasar()
const { errorMessages, graphQLErrorCodes } = useGraphErrors()
const { t } = useI18n()

async function send() {
  status.value = "loading"
  try {
    const result = await sendEmail()
    const email = result.data.sendEmailVerification.email
    status.value = "success"
    notify({
      color: "positive",
      message: t("account.email_verify.send_success_notify", {
        email,
      }),
      icon: "email",
      html: true,
    })
  } catch (error) {
    const errorMessagesList = errorMessages(
      graphQLErrorCodes(error),
      "account.failures"
    )
    if (!errorMessagesList.length) {
      errorMessagesList.push(t("failures.UNKNOWN_ERROR"))
    }
    notify({
      color: "negative",
      message: t("account.email_verify.send_failure_notify", {
        errors: errorMessagesList.join(", "),
      }),
      icon: "error",
    })
    status.value = null
  }
}
</script>

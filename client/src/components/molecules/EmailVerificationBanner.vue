<template>
  <q-banner inline-actions dense class="bg-warning">
    <template #avatar>
      <q-icon name="email" size="lg" />
    </template>
    {{ $t("account.email_verify.unverified_email_banner") }}
    <template #action>
      <email-verification-send-button />
    </template>
  </q-banner>
</template>

<script>
import EmailVerificationSendButton from "../atoms/EmailVerificationSendButton.vue";
export default {
  name: "EmailVerificationBanner",
  components: { EmailVerificationSendButton },
};
</script>

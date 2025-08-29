<template>
  <q-banner
    inline-actions
    dense
    :class="dark.isActive ? 'bg-dark-warning' : 'bg-warning'"
    class="text-black"
  >
    <template #avatar>
      <q-icon name="email" size="lg" />
    </template>
    {{ $t("account.email_verify.unverified_email_banner") }}
    <template #action>
      <email-verification-send-button />
    </template>
  </q-banner>
</template>

<script lang="ts" setup>
import EmailVerificationSendButton from "../atoms/EmailVerificationSendButton.vue"

const { dark } = useQuasar()
</script>

<template>
  <email-verification-banner v-if="!currentUser?.email_verified_at" />
  <router-view />
</template>

<script setup lang="ts">
import EmailVerificationBanner from "src/components/molecules/EmailVerificationBanner.vue"

import { useCurrentUser } from "src/use/user"

const { currentUser } = useCurrentUser()
</script>

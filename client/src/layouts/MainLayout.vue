<template>
  <q-layout view="lhh lpr lff">
    <app-header />

    <q-page-container>
      <q-page role="main">
        <email-verification-banner v-if="!currentUser?.email_verified_at" />
        <router-view />
      </q-page>
    </q-page-container>
    <app-footer />
  </q-layout>
</template>

<script setup>
import AppHeader from "src/components/AppHeader.vue"
import AppFooter from "src/components/AppFooter.vue"
import EmailVerificationBanner from "src/components/molecules/EmailVerificationBanner.vue"

import { useCurrentUser } from "src/use/user"

const { currentUser } = useCurrentUser()
</script>

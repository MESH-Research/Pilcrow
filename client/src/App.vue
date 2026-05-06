<template>
  <router-view />
</template>

<script setup lang="ts">
import { useApplyUserPreferences } from "src/use/applyUserPreferences"

// Mirror the current user's stored theme + accessible-pattern preference
// onto the app shell. Safe to call before login: with no user the
// composable's reactive sources are null and Quasar stays on the
// default (auto) theme.
useApplyUserPreferences()
</script>

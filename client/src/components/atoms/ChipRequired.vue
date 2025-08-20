<template>
  <q-chip
    v-if="$props.required"
    color="secondary"
    class="q-mx-none q-mb-none"
    text-color="white"
    :clickable="$props.canToggle"
    >{{ $t(`required`) }}</q-chip
  >
  <q-chip
    v-else
    :color="darkModeStatus ? `grey-10` : `grey-3`"
    class="q-mx-none q-mb-none"
    :clickable="$props.canToggle"
    >{{ $t(`optional`) }}</q-chip
  >
</template>

<script setup lang="ts">
import { useDarkMode } from "src/use/guiElements"
const { darkModeStatus } = useDarkMode()

defineProps({
  required: {
    type: Boolean,
    required: false,
    default: false
  },
  canToggle: {
    type: Boolean,
    required: false,
    default: false
  }
})
</script>

<template>
  <div>
    <!-- eslint-disable-next-line vue/no-v-html -->
    <p v-html="$t('auth.password_meter.summary', { score, crackTime })" />
    <div v-if="suggestions.length" class="password-suggestions">
      <q-list dense>
        <q-item v-if="warning.length" class="warning">
          <q-item-section avatar>
            <q-icon class="text-red" name="warning" />
          </q-item-section>
          {{ warning }}
        </q-item>
        <q-item
          v-for="(message, index) in suggestions"
          :key="index"
          class="suggestion"
        >
          <q-item-section avatar>
            <q-icon color="primary" name="close" />
          </q-item-section>
          {{ message }}
        </q-item>
      </q-list>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { ZxcvbnResult } from "@zxcvbn-ts/core"

interface Props {
  complexity?: ZxcvbnResult
}

const { complexity = null } = defineProps<Props>()

const suggestions = computed(() => {
  return complexity?.feedback.suggestions ?? []
})

const warning = computed(() => {
  return complexity?.feedback.warning
})

const score = computed(() => {
  return complexity?.score
})

const crackTime = computed(() => {
  return complexity?.crackTimesDisplay.offlineSlowHashing1e4PerSecond
})
</script>

<style lang="sass" scoped>
p:last-child
  margin: 0
</style>

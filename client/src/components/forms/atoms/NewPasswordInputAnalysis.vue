<template>
  <div>
    <!-- eslint-disable-next-line vue/no-v-html -->
    <p v-html="$t('auth.password_meter.summary', { score, crack_time })" />
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

<script setup>
import { computed } from "vue"
const props = defineProps({
  complexity: {
    type: Object,
    required: true,
  },
})

const suggestions = computed(() => {
  return props.complexity.feedback.suggestions
})

const warning = computed(() => {
  return props.complexity.feedback.warning
})

const score = computed(() => {
  return props.complexity.score
})

const crack_time = computed(() => {
  return props.complexity.crack_times_display
    .offline_slow_hashing_1e4_per_second
})
</script>

<style lang="sass" scoped>
p:last-child
  margin: 0
</style>

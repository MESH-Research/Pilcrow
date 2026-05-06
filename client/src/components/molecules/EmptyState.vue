<template>
  <div class="empty-state col q-pa-xl flex flex-center">
    <div class="empty-state__content text-center">
      <q-icon
        v-if="resolvedIcon"
        :name="resolvedIcon"
        size="5rem"
        class="empty-state__icon text-grey-5"
      />
      <div
        v-if="message"
        class="empty-state__message text-body1 text-grey-7 q-mt-md"
      >
        {{ message }}
      </div>
      <div v-if="slots.default" class="empty-state__actions q-mt-lg">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, useSlots } from "vue"

interface EmptyStateProps {
  icon?: string
  message?: string
  /**
   * When non-empty, the component treats this as an "empty due to an
   * active search" state and falls back to a `search_off` icon when
   * `icon` isn't explicitly provided. Useful when wiring up to
   * q-table's `#no-data` slot scope — pass `filter` straight through
   * and the component handles the icon swap.
   */
  searchTerm?: string | null
}

const props = defineProps<EmptyStateProps>()
const slots = useSlots()

const resolvedIcon = computed(
  () => props.icon ?? (props.searchTerm ? "search_off" : undefined)
)
</script>

<style scoped>
.empty-state__content {
  max-width: 32rem;
}
</style>

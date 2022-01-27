<template>
  <q-card-section
    class="bg-secondary text-white q-mx-md"
    :class="{ 'q-py-sm': !firstSection }"
  >
    <div :class="headerClass"><slot></slot></div>
  </q-card-section>
</template>

<script setup>
import { computed } from "vue"
const props = defineProps({
  firstSection: {
    type: Boolean,
    default: false,
  },
})
const headerClass = computed(() => {
  return props.firstSection ? "text-h4" : "text-subtitle2"
})
</script>

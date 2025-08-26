<template>
  <q-card-section
    class="bg-secondary text-white q-mx-md"
    :class="{ 'q-py-sm': !firstSection }"
  >
    <div><slot></slot></div>
  </q-card-section>
</template>

<script setup lang="ts">
defineProps({
  firstSection: {
    type: Boolean,
    default: false
  }
})
</script>

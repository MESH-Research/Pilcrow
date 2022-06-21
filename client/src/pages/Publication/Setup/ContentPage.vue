<template>
  <div v-if="!publication" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else class="q-pa-md"></article>
</template>

<script setup>
defineProps({
  publication: {
    type: Object,
    required: true,
  },
})
</script>

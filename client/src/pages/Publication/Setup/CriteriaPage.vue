<template>
  <article class="q-pa-md">
    <div class="column q-gutter-md">
      <publication-style-criteria :publication="publication" />
    </div>
  </article>
</template>

<script setup>
import PublicationStyleCriteria from "src/components/PublicationStyleCriteria.vue"
defineProps({
  publication: {
    type: Object,
    required: true,
  },
})
</script>

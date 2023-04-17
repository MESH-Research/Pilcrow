<template>
  <q-btn
    label="Export Submission"
    color="accent"
    icon="exit_to_app"
    :to="{
      name: 'submission_export',
      params: { id: submission.id },
    }"
    />
</template>
<script setup>
defineProps({
  submission: {
    type: Object,
    default: () => {},
  },
})
</script>

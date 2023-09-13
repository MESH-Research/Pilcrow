<template>
  <q-header class="bg-light-grey text-black" data-cy="submission-toolbar">
    <q-toolbar class="shadow-3 review-header">
      <q-btn
        :aria-label="$t('submission.toolbar.back')"
        dense
        flat
        round
        icon="arrow_back_ios_new"
        :to="{
          name: 'submission:draft',
          params: { id: props.submission.id },
        }"
      />
      <q-toolbar-title class="q-pt-xs q-pb-sm col-grow">
        <div>
          <h1 data-cy="submission_title" class="text-h3 q-ma-none">
            {{ submission.title }}
          </h1>
          <q-chip
            data-cy="submission_status"
            class="q-ma-none"
            icon="radio_button_checked"
            color="primary"
            text-color="white"
          >
            {{ $t(`submission.status.${submission.status}`) }}
          </q-chip>
        </div>
      </q-toolbar-title>
      <q-btn
        :to="{
          name: 'submission:content',
          params: { id: submission.id },
        }"
        >Re-Upload / Modify Content</q-btn
      >
    </q-toolbar>
  </q-header>
</template>
<script setup>
const props = defineProps({
  // Drawer status
  commentDrawerOpen: {
    type: Boolean,
    default: null,
  },
  highlightVisibility: {
    type: Boolean,
    default: true,
  },
  submission: {
    type: Object,
    default: null,
  },
})
</script>

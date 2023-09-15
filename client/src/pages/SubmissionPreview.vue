<template>
  <div data-cy="submission_review_page">
    <div v-if="loading" class="q-pa-lg">
      {{ $t("loading") }}
    </div>
    <div
      v-else-if="!submission?.content && submission?.status === 'DRAFT'"
      class="q-pa-xl items-center column content-center text-center"
    >
      <p style="max-width: 20rem" data-cy="explanation">
        {{ $t("submissions.draft_with_no_content") }}
      </p>
      <q-btn
        data-cy="draft_btn"
        :label="$t(`submissions.action.draft`)"
        color="primary"
        class="q-mt-md"
        :to="{
          name: 'submission:draft',
          params: { id: submission.id },
        }"
      />
    </div>
    <article v-else>
      <q-layout
        data-cy="submission_preview_layout"
        view="hHh lpR fFr"
        container
        style="min-height: calc(100vh - 70px)"
      >
        <submission-preview-toolbar :id="id" :submission="submission" />

        <q-page-container>
          <q-banner inline-actions class="bg-positive text-white text-center">
            You are previewing this submission.
          </q-banner>
          <submission-content :annotation-enabled="false" :highlight-visibility="false" />
          <div class="flex justify-center q-mb-xl">
          <q-btn
            color="primary"
            label="Update Content"
            square
            :to="{
              name: 'submission:content',
              params: { id: id },
            }"
          /></div>
        </q-page-container>
      </q-layout>

      <div class="row q-col-gutter-lg q-pa-lg"></div>
    </article>
  </div>
</template>

<script setup>
import SubmissionContent from "src/components/atoms/SubmissionContent.vue"
import SubmissionPreviewToolbar from "src/components/atoms/SubmissionPreviewToolbar.vue"
import { provide, computed } from "vue"
import { GET_SUBMISSION_REVIEW } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})
const { loading, result } = useQuery(GET_SUBMISSION_REVIEW, { id: props.id })
const submission = computed(() => {
  return result.value?.submission
})
provide("submission", submission)
provide("commentDrawerOpen", null)
provide("activeComment", null)
</script>

<style lang="sass" scoped>
.page-seperator
  height: 3px
  background-color: #888
</style>

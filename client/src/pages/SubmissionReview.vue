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
          params: { id: props.id }
        }"
      />
    </div>
    <article v-else>
      <q-layout
        data-cy="submission_review_layout"
        view="hHh lpR fFr"
        container
        style="min-height: calc(100vh - 118px - 51px)"
      >
        <submission-toolbar
          :id="id"
          v-model:comment-drawer-open="commentDrawerOpen"
          v-model:highlight-visibility="highlightVisibility"
          :submission="submission"
        />
        <submission-comment-drawer v-model:drawer-open="commentDrawerOpen" />
        <q-page-container>
          <submission-content
            v-model:highlight-visibility="highlightVisibility"
            @scroll-to-overall-comments="handleScroll"
            @scroll-add-new-overall-comment="handleNewScroll"
          />
          <q-separator class="page-seperator" />
          <div ref="scrollOverallComments"></div>
          <submission-comment-section />
          <div ref="scrollAddNewOverallComment"></div>
        </q-page-container>
      </q-layout>
    </article>
  </div>
</template>

<script setup lang="ts">
import SubmissionCommentDrawer from "src/components/atoms/SubmissionCommentDrawer.vue"
import SubmissionCommentSection from "src/components/atoms/SubmissionCommentSection.vue"
import SubmissionContent from "src/components/atoms/SubmissionContent.vue"
import SubmissionToolbar from "src/components/atoms/SubmissionToolbar.vue"
import { ref, computed } from "vue"
import { GET_SUBMISSION_REVIEW } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import { scroll } from "quasar"
import { provideSubmissionReviewContext } from "src/use/submissionContext"
const { getScrollTarget, setVerticalScrollPosition } = scroll

interface Props {
  id: string
}
const props = defineProps<Props>()
const { loading, result } = useQuery(GET_SUBMISSION_REVIEW, { id: props.id })
const submission = computed(() => {
  return result.value?.submission
})
const highlightVisibility = ref(true)
const commentDrawerOpen = ref(false)
provideSubmissionReviewContext({
  submission,
  commentDrawerOpen
})

const scrollOverallComments = ref(null)
const scrollAddNewOverallComment = ref(null)

function handleScroll() {
  const scrollValue = scrollOverallComments.value
  const scrollTarget = getScrollTarget(scrollValue)
  setVerticalScrollPosition(scrollTarget, scrollValue.offsetTop, 250)
}

function handleNewScroll() {
  const scrollValue = scrollAddNewOverallComment.value
  const scrollTarget = getScrollTarget(scrollValue)
  setVerticalScrollPosition(scrollTarget, scrollValue.offsetTop, 250)
}
</script>

<style lang="sass" scoped>
.page-seperator
  height: 3px
  background-color: #888
</style>

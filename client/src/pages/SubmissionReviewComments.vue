<template>
  <div data-cy="submission_review_comments_page">
    <div v-if="loading" class="q-pa-lg">
      {{ $t("loading") }}
    </div>
    <article v-else>
      <q-layout
        data-cy="submission_review_comments_layout"
        view="hHh lpR fFr"
        container
        style="min-height: calc(100vh - 70px)"
      >
        <submission-comment-drawer v-model:drawer-open="commentDrawerOpen" />
        <q-btn
          v-if="submission"
          class="q-mt-lg"
          :label="$t(`export.download.title`)"
          color="accent"
          icon="file_download"
          :href="blob"
          :download="`submission_${submission.id}.html`"
        />
        <q-page-container ref="comments-content">
          <submission-content
            v-model:highlight-visibility="highlightVisibility"
            @scroll-to-overall-comments="handleScroll"
            @scroll-add-new-overall-comment="handleNewScroll"
          />
          <q-separator class="page-seperator" />
          <div ref="scrollOverallComments"></div>

          <inline-comments />
          <submission-comment-section />
          <div ref="scrollAddNewOverallComment"></div>
        </q-page-container>
      </q-layout>

      <div class="row q-col-gutter-lg q-pa-lg"></div>
    </article>
  </div>
</template>

<script setup>
import InlineComments from "src/components/molecules/InlineComments.vue"
import SubmissionCommentDrawer from "src/components/atoms/SubmissionCommentDrawer.vue"
import SubmissionCommentSection from "src/components/atoms/SubmissionCommentSection.vue"
import SubmissionContent from "src/components/atoms/SubmissionContent.vue"
import { ref, provide, computed, useTemplateRef, watch } from "vue"
import { GET_SUBMISSION_REVIEW } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import { scroll } from "quasar"
const { getScrollTarget, setVerticalScrollPosition } = scroll

const props = defineProps({
  id: {
    type: String,
    required: true
  }
})
const { loading, result } = useQuery(GET_SUBMISSION_REVIEW, { id: props.id })
const submission = computed(() => {
  return result.value?.submission
})
const highlightVisibility = ref(true)
const commentDrawerOpen = ref(false)
provide("submission", submission)
provide("activeComment", ref(null))
provide("commentDrawerOpen", commentDrawerOpen)

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
  scrollValue
}

const comments_content = useTemplateRef("comments-content")
let blob = ""
let download_content = "Default"

watch([comments_content], () => {
  download_content = comments_content.value.$el.innerHTML
  blob = computed(() =>
    URL.createObjectURL(new Blob([download_content], { type: "text/html" }))
  )
})
</script>

<style lang="sass" scoped>
.page-seperator
  height: 3px
  background-color: #888
</style>

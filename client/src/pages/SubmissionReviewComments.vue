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
        style="min-height: calc(100vh - 118px)"
      >
        <q-page-container ref="comments-content">
          <div class="row justify-end q-pa-md">
            <q-btn
              v-if="submission"
              :label="$t(`export.download.title`)"
              color="accent"
              icon="file_download"
              :href="blob"
              :download="`submission_${submission.id}.html`"
            />
          </div>
          <submission-content
            v-model:highlight-visibility="highlightVisibility"
            @scroll-to-overall-comments="handleScroll"
            @scroll-add-new-overall-comment="handleNewScroll"
          />
          <submission-comment-drawer v-model:drawer-open="commentDrawerOpen" />
          <q-separator class="page-seperator" />
          <div ref="scrollOverallComments"></div>
          <submission-comment-section />
          <div ref="scrollAddNewOverallComment"></div>
        </q-page-container>
      </q-layout>
    </article>
  </div>
</template>

<script setup>
import SubmissionCommentDrawer from "src/components/atoms/SubmissionCommentDrawer.vue"
import SubmissionCommentSection from "src/components/atoms/SubmissionCommentSection.vue"
import SubmissionContent from "src/components/atoms/SubmissionContent.vue"
import { ref, provide, computed, useTemplateRef, watch } from "vue"
import { GET_SUBMISSION_REVIEW } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import exportStyles from "src/components/styles/exportStyles"
import { scroll } from "quasar"
provide("activeComment", ref(null))
provide("forExport", ref(true))
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
provide("submission", submission)
const highlightVisibility = ref(true)
const commentDrawerOpen = ref(false)
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

function updateBlob(message) {
  console.log("run", message, comments_content.value?.$el.innerHTML)
  let download_content = comments_content.value?.$el.innerHTML
  blob = computed(() =>
    URL.createObjectURL(
      new Blob(
        [
          `<html><head>`,
          `<title>Submission Review Comments</title>`,
          `<style>${exportStyles}</style>`,
          `</head><body>`,
          download_content,
          `</body></html>`
        ],
        { type: "text/html" }
      )
    )
  )
}

watch([comments_content], () => {
  updateBlob("1")
})
watch(result, () => updateBlob("2"))
</script>

<style lang="sass" scoped>
.page-seperator
  height: 3px
  background-color: #888
</style>

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
          <div class="row justify-between q-pa-md">
            <q-btn
              :aria-label="$t('submission.toolbar.back')"
              dense
              flat
              round
              icon="arrow_back_ios_new"
              :to="{
                name: 'submission:export',
                params: { id: props.id }
              }"
            />
            <q-btn
              :label="$t('export.comments.download')"
              color="accent"
              icon="file_download"
              :href="blob"
              :download="`submission_${props.id}.html`"
            />
          </div>
          <submission-content
            v-model:highlight-visibility="highlightVisibility"
            :show-overall-comments="showOverallComments"
            @scroll-to-overall-comments="handleScroll"
            @scroll-add-new-overall-comment="handleNewScroll"
            @editor-ready="updateBlob('from editor')"
          />
          <submission-comment-drawer
            v-if="showInlineComments"
            v-model:drawer-open="commentDrawerOpen"
          />
          <q-separator class="page-seperator" />
          <div ref="scrollOverallComments"></div>
          <submission-comment-section v-if="showOverallComments" />
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
import { useRoute } from "vue-router"
import { useI18n } from "vue-i18n"

provide("activeComment", ref(null))
provide("forExport", ref(true))
const { t } = useI18n()
const route = useRoute()
const { getScrollTarget, setVerticalScrollPosition } = scroll

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  exportOptionChoice: {
    type: String,
    default: "io"
  }
})
const exportOptionChoiceMapper = {
  io: {
    id: props.id,
    skip_inline: false,
    skip_overall: false
  },
  i: {
    id: props.id,
    skip_inline: false,
    skip_overall: true
  },
  o: {
    id: props.id,
    skip_inline: true,
    skip_overall: false
  }
}

const exportOptionChoiceObject =
  exportOptionChoiceMapper[route.query.export ?? props.exportOptionChoice]

const commenters_array = route.query.ids?.map(Number)
if (commenters_array && typeof commenters_array == "object") {
  exportOptionChoiceObject.sic = "I_D"
  exportOptionChoiceObject.sirc = "I_D"
  exportOptionChoiceObject.soc = "I_D"
  exportOptionChoiceObject.sorc = "I_D"
  exportOptionChoiceObject.commenters_array = commenters_array
}

const showInlineComments = !exportOptionChoiceObject.skip_inline ?? true
const showOverallComments = !exportOptionChoiceObject.skip_overall ?? true

const { loading, result } = useQuery(
  GET_SUBMISSION_REVIEW,
  exportOptionChoiceObject
)
const submission = computed(() => {
  return result.value?.submission
})
const highlightVisibility = ref(true)
const commentDrawerOpen = ref(false)
provide("commentDrawerOpen", commentDrawerOpen)
provide("submission", submission)

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
let blob = ref("")

function updateBlob() {
  let download_content = comments_content.value?.$el.innerHTML
  const scripts = `<script>
    const annotations = document.querySelectorAll('[data-context-id]')
    const inline_comments = document.querySelectorAll('[aria-label="Go To Highlight"]')
    function scrollTo(hash) {
      location.hash = "#" + hash;
    }
    for (const annotation of annotations) {
      annotation.addEventListener("click", (e) => {
        const context_id = e.target.attributes["data-context-id"].value;
        scrollTo('inline-comment-' + context_id);
      })
    }
    for (const comment of inline_comments) {
      comment.addEventListener("click", (e) => {
        const context_id = e.target.attributes["data-context-id"].value;
        scrollTo('comment-highlight-' + context_id);
      })
    }
  <\/script>`
  blob.value = URL.createObjectURL(
    new Blob(
      [
        `<html><head>`,
        `<title>${t("export.submission_review_comments")}</title>`,
        `<style>${exportStyles}</style>`,
        `</head><body>`,
        download_content,
        scripts,
        `</body></html>`
      ],
      { type: "text/html" }
    )
  )
}

watch([comments_content], () => {
  updateBlob("template")
})
</script>

<style lang="sass" scoped>
.page-seperator
  height: 3px
  background-color: #888
</style>

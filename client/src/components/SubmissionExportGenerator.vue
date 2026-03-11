<template>
  <div style="position: absolute; left: -9999px">
    <div ref="commentsContent">
      <submission-content
        v-model:highlight-visibility="highlightVisibility"
        :show-overall-comments="showOverallComments"
        @scroll-to-overall-comments="() => {}"
        @scroll-add-new-overall-comment="() => {}"
        @editor-ready="updateBlob"
      />
      <submission-comment-drawer
        v-if="showInlineComments"
        v-model:drawer-open="commentDrawerOpen"
      />
      <q-separator class="page-seperator" />
      <submission-comment-section v-if="showOverallComments" />
    </div>
  </div>
  <q-dialog
    :model-value="previewOpen"
    maximized
    @update:model-value="emit('update:previewOpen', $event)"
  >
    <q-card>
      <q-card-section class="row items-center q-pb-none">
        <div class="text-h6">{{ $t(`export.preview`) }}</div>
        <q-space />
        <q-btn v-close-popup icon="close" flat round dense />
      </q-card-section>
      <q-card-section class="col q-pt-none" style="height: calc(100vh - 60px)">
        <iframe
          :src="blobUrl"
          style="width: 100%; height: 100%; border: none"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import SubmissionCommentDrawer from "src/components/atoms/SubmissionCommentDrawer.vue"
import SubmissionCommentSection from "src/components/atoms/SubmissionCommentSection.vue"
import SubmissionContent from "src/components/atoms/SubmissionContent.vue"
import { ref, provide, computed, watch, nextTick } from "vue"
import { GET_SUBMISSION_REVIEW } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import exportStyles from "src/components/styles/exportStyles"
import { useI18n } from "vue-i18n"

const props = defineProps({
  id: {
    type: String,
    required: true
  },
  options: {
    type: Object,
    default: () => ({
      includeInline: false,
      includeOverall: false,
      createdBy: []
    })
  },
  previewOpen: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(["update:blob", "update:previewOpen"])

provide("activeComment", ref(null))
provide("forExport", ref(true))
const { t } = useI18n()

const exportOptionChoiceObject = computed(() => {
  const { includeInline, includeOverall, createdBy } = props.options
  const obj = {
    id: props.id,
    skip_inline: !includeInline,
    skip_overall: !includeOverall
  }
  if (createdBy?.length) {
    obj.createdBy = createdBy
  }
  return obj
})

const showInlineComments = computed(
  () => !exportOptionChoiceObject.value.skip_inline
)
const showOverallComments = computed(
  () => !exportOptionChoiceObject.value.skip_overall
)

const { result } = useQuery(GET_SUBMISSION_REVIEW, exportOptionChoiceObject)
const submission = computed(() => result.value?.submission)
const highlightVisibility = ref(true)
const commentDrawerOpen = ref(false)
provide("commentDrawerOpen", commentDrawerOpen)
provide("submission", submission)

const commentsContent = ref(null)
const blobUrl = ref("")

function updateBlob() {
  const el = commentsContent.value
  if (!el) return
  const doc = new DOMParser().parseFromString(el.innerHTML, "text/html")
  doc.title = t("export.submission_review_comments")
  const style = doc.createElement("style")
  style.textContent = exportStyles
  doc.head.appendChild(style)
  for (const highlight of doc.querySelectorAll("[data-context-id]")) {
    const id = highlight.getAttribute("data-context-id")
    highlight.setAttribute("href", `#inline-comment-${id}`)
  }
  for (const link of doc.querySelectorAll('[aria-label="Go To Highlight"]')) {
    const id = link.getAttribute("data-context-id")
    link.setAttribute("href", `#comment-highlight-${id}`)
  }
  blobUrl.value = URL.createObjectURL(
    new Blob([doc.documentElement.outerHTML], { type: "text/html" })
  )
  emit("update:blob", blobUrl.value)
}

watch(commentsContent, () => updateBlob())
watch(submission, () => nextTick(() => updateBlob()))
</script>

<style lang="sass" scoped>
.page-seperator
  height: 3px
  background-color: #888
</style>

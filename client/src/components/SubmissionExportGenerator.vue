<template>
  <div style="display: none">
    <div ref="exportContent">
      <submission-content
        v-model:highlight-visibility="highlightVisibility"
        :show-overall-comments="showOverallComments"
        @scroll-to-overall-comments="() => {}"
        @scroll-add-new-overall-comment="() => {}"
        @editor-ready="updateBlob"
      />
      <hr
        v-if="showInlineComments || showOverallComments"
        class="page-separator"
      />
      <export-comment-list
        v-if="showInlineComments"
        :heading="$t('submissions.inline_comments.heading')"
        :comments="submission?.inline_comments ?? []"
        :number-map="inlineNumberMap"
        sort-by="from"
      />
      <export-comment-list
        v-if="showOverallComments"
        :heading="$t('submissions.overall_comments.heading')"
        :comments="submission?.overall_comments ?? []"
        :number-map="overallNumberMap"
      />
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
        <q-btn
          :label="$t(`export.download.title`)"
          color="accent"
          icon="file_download"
          :href="blobUrl"
          :download="downloadFilename"
          :disable="!blobUrl"
          flat
          dense
        />
        <q-btn v-close-popup icon="close" flat round dense />
      </q-card-section>
      <q-card-section class="col q-pt-none" style="height: calc(100vh - 60px)">
        <iframe
          :src="blobUrl"
          style="
            background-color: #fff;
            width: 100%;
            height: 100%;
            border: none;
          "
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup>
import ExportCommentList from "src/components/export/ExportCommentList.vue"
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
  },
  downloadFilename: {
    type: String,
    default: "export.html"
  }
})

const emit = defineEmits(["update:blob", "update:previewOpen"])

// SubmissionContent still requires these injections
provide("activeComment", ref(null))
provide("forExport", ref(true))
provide("commentDrawerOpen", ref(false))
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
provide("submission", submission)
const notDeleted = (c) => c.deleted_at === null
const inlineNumberMap = computed(() => {
  const map = {}
  let num = 1
  for (const c of (submission.value?.inline_comments ?? []).filter(
    notDeleted
  )) {
    map[c.id] = num++
  }
  return map
})
const overallNumberMap = computed(() => {
  const map = {}
  let num = 1
  for (const c of (submission.value?.overall_comments ?? []).filter(
    notDeleted
  )) {
    map[c.id] = num++
  }
  return map
})

const exportContent = ref(null)
const blobUrl = ref("")

function updateBlob() {
  const el = exportContent.value
  if (!el) return
  const doc = new DOMParser().parseFromString(el.innerHTML, "text/html")
  doc.title = t("export.submission_review_comments")
  const style = doc.createElement("style")
  style.textContent = exportStyles
  doc.head.appendChild(style)
  const numMap = inlineNumberMap.value
  for (const highlight of doc.querySelectorAll(
    ".comment-highlight[data-context-id]"
  )) {
    const id = highlight.getAttribute("data-context-id")
    highlight.setAttribute("href", `#inline-comment-${id}`)
    if (numMap[id]) {
      highlight.setAttribute("data-comment-number", numMap[id])
    }
  }
  blobUrl.value = URL.createObjectURL(
    new Blob([doc.documentElement.outerHTML], { type: "text/html" })
  )
  emit("update:blob", blobUrl.value)
}

watch(exportContent, () => updateBlob())
watch(submission, () => nextTick(() => updateBlob()))
</script>

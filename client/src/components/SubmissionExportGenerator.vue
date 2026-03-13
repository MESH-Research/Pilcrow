<template>
  <div style="display: none">
    <div ref="exportContainer">
      <export-content
        v-if="submission"
        :content="submission.content"
        :inline-comments="submission.inline_comments ?? []"
        :highlight-visibility="highlightVisibility"
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
    :title="$t('export.preview.title')"
    @update:model-value="emit('update:previewOpen', $event)"
  >
    <q-card>
      <q-card-section class="row items-center q-gutter-sm">
        <div class="text-h4">{{ $t(`export.preview.title`) }}</div>
        <q-space />
        <q-btn
          :label="$t(`export.download.title`)"
          color="accent"
          icon="file_download"
          :href="blobUrl"
          :download="downloadFilename"
          :disable="!blobUrl"
        />
        <q-btn
          v-close-popup
          icon="close"
          flat
          round
          dense
          :aria-label="$t('export.preview.close')"
        />
      </q-card-section>
      <q-card-section class="col q-pt-none" style="height: calc(100vh - 80px)">
        <iframe
          ref="previewIframe"
          :srcdoc="exportHtml"
          :title="$t('export.preview.title')"
          style="
            background-color: #fff;
            width: 100%;
            height: 100%;
            border: 1px solid #ddd;
          "
          @load="attachIframeLinkHandler"
        />
      </q-card-section>
    </q-card>
  </q-dialog>
</template>

<script setup lang="ts">
import ExportCommentList from "src/components/export/ExportCommentList.vue"
import ExportContent from "src/components/export/ExportContent.vue"
import { ref, computed, watch, nextTick } from "vue"
import { GET_SUBMISSION_REVIEW } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import exportStyles from "src/components/styles/exportStyles"
import { useI18n } from "vue-i18n"

interface ExportOptions {
  includeInline: boolean
  includeOverall: boolean
  createdBy: string[]
}

interface Props {
  id: string
  options?: ExportOptions
  previewOpen?: boolean
  downloadFilename?: string
}

interface Emits {
  "update:blob": [value: string]
  "update:previewOpen": [value: boolean]
}

const props = withDefaults(defineProps<Props>(), {
  options: () => ({
    includeInline: false,
    includeOverall: false,
    createdBy: []
  }),
  previewOpen: false,
  downloadFilename: "export.html"
})

const emit = defineEmits<Emits>()

const { t } = useI18n()

const exportOptionChoiceObject = computed(() => {
  const { includeInline, includeOverall, createdBy } = props.options
  const obj: Record<string, unknown> = {
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
const notDeleted = (c: Record<string, unknown>) => c.deleted_at === null
const inlineNumberMap = computed(() => {
  const map: Record<string, number> = {}
  let num = 1
  for (const c of (submission.value?.inline_comments ?? []).filter(
    notDeleted
  )) {
    map[c.id as string] = num++
  }
  return map
})
const overallNumberMap = computed(() => {
  const map: Record<string, number> = {}
  let num = 1
  for (const c of (submission.value?.overall_comments ?? []).filter(
    notDeleted
  )) {
    map[c.id as string] = num++
  }
  return map
})

const exportContainer = ref<HTMLElement | null>(null)
const previewIframe = ref<HTMLIFrameElement | null>(null)
const exportHtml = ref("")
const blobUrl = ref("")

function attachIframeLinkHandler() {
  const iframe = previewIframe.value
  if (!iframe?.contentDocument) return
  iframe.contentDocument.addEventListener("click", (e: MouseEvent) => {
    const el = e.target as HTMLElement | null
    const a = el?.closest('a[href^="#"]')
    if (!a) return
    e.preventDefault()
    const href = a.getAttribute("href")
    if (!href) return
    const target = iframe.contentDocument?.getElementById(href.slice(1))
    if (target) target.scrollIntoView({ behavior: "smooth" })
  })
}

function updateBlob() {
  const el = exportContainer.value
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
    const link = doc.createElement("a")
    link.href = `#inline-comment-${id}`
    link.className = "comment-highlight-link"
    highlight.parentNode.insertBefore(link, highlight)
    link.appendChild(highlight)
    if (id && numMap[id]) {
      highlight.setAttribute("data-comment-number", String(numMap[id]))
    }
  }
  const html = doc.documentElement.outerHTML
  exportHtml.value = html
  blobUrl.value = URL.createObjectURL(new Blob([html], { type: "text/html" }))
  emit("update:blob", blobUrl.value)
}

watch(exportContainer, () => updateBlob())
watch(submission, () => nextTick(() => updateBlob()))
</script>

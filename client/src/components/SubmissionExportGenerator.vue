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
</template>

<script setup lang="ts">
import ExportCommentList from "src/components/export/ExportCommentList.vue"
import ExportContent from "src/components/export/ExportContent.vue"
import { ref, computed, watch, nextTick } from "vue"
import exportStyles from "src/components/styles/exportStyles"
import { useI18n } from "vue-i18n"
import type { ExportCommentBase } from "src/components/export/ExportComment.vue"

interface ExportSubmission {
  content?: { data: string } | null
  inline_comments?: ExportCommentBase[]
  overall_comments?: ExportCommentBase[]
}

interface Props {
  submission?: ExportSubmission | null
  includeInline?: boolean
  includeOverall?: boolean
  downloadFilename?: string
}

interface Emits {
  "update:blob": [value: string]
  "update:html": [value: string]
}

const props = withDefaults(defineProps<Props>(), {
  submission: null,
  includeInline: false,
  includeOverall: false,
  downloadFilename: "export.html"
})

const emit = defineEmits<Emits>()

const { t } = useI18n()

const showInlineComments = computed(() => props.includeInline)
const showOverallComments = computed(() => props.includeOverall)
const highlightVisibility = ref(true)
const notDeleted = (c: ExportCommentBase) => c.deleted_at === null
const inlineNumberMap = computed(() => {
  const map: Record<string, number> = {}
  let num = 1
  for (const c of (props.submission?.inline_comments ?? []).filter(
    notDeleted
  )) {
    map[c.id as string] = num++
  }
  return map
})
const overallNumberMap = computed(() => {
  const map: Record<string, number> = {}
  let num = 1
  for (const c of (props.submission?.overall_comments ?? []).filter(
    notDeleted
  )) {
    map[c.id as string] = num++
  }
  return map
})

const exportContainer = ref<HTMLElement | null>(null)

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
  emit("update:html", html)
  const blob = URL.createObjectURL(new Blob([html], { type: "text/html" }))
  emit("update:blob", blob)
}

watch(exportContainer, () => updateBlob())
watch(
  () => props.submission,
  () => nextTick(() => updateBlob())
)
</script>

<template>
  <div style="display: none">
    <div ref="exportContainer">
      <export-content
        v-if="submission"
        :submission="submission"
        :highlight-visibility="highlightVisibility"
        @editor-ready="updateBlob"
      />
      <hr v-if="includeInline || includeOverall" class="page-separator" />
      <export-inline-comments v-if="includeInline" :submission="submission" />
      <export-overall-comments v-if="includeOverall" :submission="submission" />
    </div>
  </div>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment submissionExportGenerator on Submission {
    ...exportContent
    ...exportInlineComments
    ...exportOverallComments
  }
`)
</script>

<script setup lang="ts">
import ExportContent from "src/components/export/ExportContent.vue"
import ExportInlineComments from "src/components/export/ExportInlineComments.vue"
import ExportOverallComments from "src/components/export/ExportOverallComments.vue"
import { ref, watch, nextTick } from "vue"
import exportStyles from "src/components/styles/exportStyles"
import { useI18n } from "vue-i18n"
import type { submissionExportGeneratorFragment } from "src/graphql/generated/graphql"

interface Props {
  submission?: submissionExportGeneratorFragment | null
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
  includeInline: true,
  includeOverall: true,
  downloadFilename: "export.html"
})

const emit = defineEmits<Emits>()

const { t } = useI18n()

const highlightVisibility = ref(true)
const exportContainer = ref<HTMLElement | null>(null)

function buildInlineNumberMap() {
  const map: Record<string, number> = {}
  let num = 1
  for (const c of props.submission?.inline_comments ?? []) {
    if (c.deleted_at === null) {
      map[c.id] = num++
    }
  }
  return map
}

function updateBlob() {
  const el = exportContainer.value
  if (!el) return
  const doc = new DOMParser().parseFromString(el.innerHTML, "text/html")
  doc.title = t("export.submission_review_comments")
  const style = doc.createElement("style")
  style.textContent = exportStyles
  doc.head.appendChild(style)
  const numMap = buildInlineNumberMap()
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

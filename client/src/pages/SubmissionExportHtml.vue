<template>
  <div v-if="!submission" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <div v-else>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="$t('header.submissions', 2)"
          to="/submissions"
        />
        <q-breadcrumbs-el
          :label="$t('submissions.details_heading')"
          :to="{
            name: 'submission:details',
            params: { id: submission.id }
          }"
        />
        <q-breadcrumbs-el :label="$t(`export.title`)" :to="optionsRoute" />
        <q-breadcrumbs-el :label="$t(`export.preview`)" />
      </q-breadcrumbs>
    </nav>
    <article class="q-pa-lg">
      <h2 class="q-my-none">{{ $t(`export.preview`) }}</h2>
      <div class="row q-gutter-md q-py-md">
        <q-btn
          :label="$t(`export.download.title`)"
          color="accent"
          icon="file_download"
          :href="blobUrl"
          :download="downloadFilename"
          :disable="!blobUrl"
        />
        <q-btn
          :label="$t(`export.edit_options`)"
          icon="settings"
          flat
          :to="optionsRoute"
        />
      </div>
      <iframe
        ref="previewIframe"
        :srcdoc="exportHtml"
        style="
          background-color: #fff;
          width: 100%;
          height: calc(100vh - 280px);
          border: 1px solid #ddd;
        "
        @load="attachIframeLinkHandler"
      />
    </article>
    <submission-export-generator
      :submission="submission"
      :include-inline="includeInline"
      :include-overall="includeOverall"
      :download-filename="downloadFilename"
      @update:blob="blobUrl = $event"
      @update:html="exportHtml = $event"
    />
  </div>
</template>
<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetSubmissionExportData(
    $id: ID!
    $skip_inline: Boolean = false
    $skip_overall: Boolean = false
    $createdBy: [ID!]
  ) {
    submission(id: $id) {
      id
      title
      ...submissionExportGenerator
    }
  }
`)
</script>

<script setup lang="ts">
import SubmissionExportGenerator from "../components/SubmissionExportGenerator.vue"
import { computed, ref, watch } from "vue"
import { GetSubmissionExportDataDocument } from "src/graphql/generated/graphql"
import { useQuery } from "@vue/apollo-composable"
import { useRoute } from "vue-router"

const route = useRoute()

interface Props {
  id: string
}

const props = defineProps<Props>()

const commentsParam = computed(() => {
  const val = route.query.comments
  if (val === "ALL" || val === "INLINE" || val === "OVERALL") return val
  return null
})

const hasCreatedByFilter = computed(() => {
  const val = route.query.commentsCreatedBy
  return typeof val === "string" && val.startsWith("[") && val.endsWith("]")
})
const createdByParam = computed(() => {
  const val = route.query.commentsCreatedBy
  if (typeof val !== "string") return []
  const inner = val.replace(/^\[|\]$/g, "")
  return inner ? inner.split(",").filter(Boolean) : []
})

const includeInline = computed(
  () => commentsParam.value === "ALL" || commentsParam.value === "INLINE"
)
const includeOverall = computed(
  () => commentsParam.value === "ALL" || commentsParam.value === "OVERALL"
)

const exportQueryVars = computed(() => {
  const vars: Record<string, unknown> = {
    id: props.id,
    skip_inline: !includeInline.value,
    skip_overall: !includeOverall.value
  }
  if (hasCreatedByFilter.value) {
    vars.createdBy = createdByParam.value
  }
  return vars
})

const { result } = useQuery(GetSubmissionExportDataDocument, exportQueryVars)
const submission = computed(() => result.value?.submission)

const optionsRoute = computed(() => {
  const { action, ...query } = route.query
  return {
    name: "submission:export",
    params: { id: props.id },
    query
  }
})

const downloadFilename = computed(() => `submission_${props.id}_export.html`)

const blobUrl = ref("")
const exportHtml = ref("")
const previewIframe = ref<HTMLIFrameElement | null>(null)

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

const autoDownload = route.query.action === "download"
watch(blobUrl, (url) => {
  if (!autoDownload || !url) return
  const a = document.createElement("a")
  a.href = url
  a.download = downloadFilename.value
  a.click()
})
</script>

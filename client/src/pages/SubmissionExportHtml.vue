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
        <q-breadcrumbs-el
          :label="$t(`export.title`)"
          :to="{
            name: 'submission:export',
            params: { id: submission.id }
          }"
        />
        <q-breadcrumbs-el :label="$t(`export.preview`)" />
      </q-breadcrumbs>
    </nav>
    <article class="q-pa-lg">
      <q-card>
        <q-card-section class="row items-center q-gutter-sm">
          <div class="text-h4">{{ $t(`export.preview`) }}</div>
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
            :label="$t(`export.title`)"
            icon="settings"
            flat
            :to="{
              name: 'submission:export',
              params: { id }
            }"
          />
        </q-card-section>
        <q-card-section
          class="col q-pt-none"
          style="height: calc(100vh - 200px)"
        >
          <iframe
            ref="previewIframe"
            :srcdoc="exportHtml"
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
  fragment exportInlineComment on InlineComment {
    from
    to
    ...commentFields
    style_criteria {
      id
      name
      icon
    }
    replies {
      ...commentFields
      parent_id
      reply_to_id
      read_at
    }
    read_at
  }
`)

graphql(`
  fragment exportOverallComment on OverallComment {
    ...commentFields
    replies {
      ...commentFields
      parent_id
      reply_to_id
      read_at
    }
    read_at
  }
`)

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
      content {
        data
      }
      inline_comments(createdBy: $createdBy) @skip(if: $skip_inline) {
        ...exportInlineComment
      }
      overall_comments(createdBy: $createdBy) @skip(if: $skip_overall) {
        ...exportOverallComment
      }
    }
  }
`)
</script>

<script setup lang="ts">
import SubmissionExportGenerator from "../components/SubmissionExportGenerator.vue"
import { computed, ref } from "vue"
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
  if (val === "INLINE" || val === "OVERALL") return val
  return null
})

const createdByParam = computed(() => {
  const val = route.query.createdBy
  if (typeof val !== "string" || !val) return []
  return val.split(",").filter(Boolean)
})

const includeInline = computed(() => commentsParam.value !== "OVERALL")
const includeOverall = computed(() => commentsParam.value !== "INLINE")

const exportQueryVars = computed(() => {
  const vars: Record<string, unknown> = {
    id: props.id,
    skip_inline: !includeInline.value,
    skip_overall: !includeOverall.value
  }
  if (createdByParam.value.length) {
    vars.createdBy = createdByParam.value
  }
  return vars
})

const { result } = useQuery(GetSubmissionExportDataDocument, exportQueryVars)
const submission = computed(() => result.value?.submission)

const downloadFilename = computed(() => {
  const base = `submission_${props.id}`
  const hasComments = includeInline.value || includeOverall.value
  return hasComments ? `${base}_comments.html` : `${base}.html`
})

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
</script>

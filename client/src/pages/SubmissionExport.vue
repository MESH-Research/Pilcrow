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
        <q-breadcrumbs-el :label="$t(`export.title`)" />
      </q-breadcrumbs>
    </nav>
    <article class="q-pa-lg">
      <h2 class="q-my-none">{{ $t(`export.title`) }}</h2>
      <section class="q-gutter-md">
        <section>
          <h3>{{ submission.title }}</h3>
          <p>{{ $t(`export.description`) }}</p>
          <p>{{ $t(`export.download.description`) }}</p>
        </section>
        <q-card square bordered flat>
          <q-card-section>
            <h4 class="q-my-none text-bold">
              {{ $t("export.comments.title") }}
            </h4>
            <q-checkbox
              v-model="include_comments"
              :label="$t('export.comments.include')"
            />
            <q-option-group
              v-if="include_comments"
              v-model="comment_type"
              :options="comment_options"
              type="radio"
              class="q-ml-md"
            />
            <q-checkbox
              v-if="include_comments"
              v-model="filter_participants"
              :label="$t('export.participants.filter')"
            />
            <export-participant-selector
              v-if="include_comments && filter_participants"
              v-model="export_participants"
              :submission-id="id"
              :commenter-type="comment_type"
            />
          </q-card-section>
        </q-card>
      </section>
      <div v-if="submission" class="row q-gutter-md q-py-md">
        <q-btn
          :label="$t(`export.download.title`)"
          color="accent"
          icon="file_download"
          :href="downloadBlob"
          :download="downloadFilename"
          :disable="!downloadBlob"
        />
        <q-btn
          :label="$t(`export.preview.action`)"
          color="primary"
          icon="visibility"
          :disable="!downloadBlob"
          @click="showPreview = true"
        />
      </div>
      <q-spinner v-else />
    </article>
    <submission-export-generator
      v-if="submission"
      :id="id"
      v-model:preview-open="showPreview"
      :options="exportOptions"
      :download-filename="downloadFilename"
      @update:blob="downloadBlob = $event"
    />
  </div>
</template>
<script setup lang="ts">
import ExportParticipantSelector from "../components/atoms/ExportParticipantSelector.vue"
import SubmissionExportGenerator from "../components/SubmissionExportGenerator.vue"
import { computed, ref } from "vue"
import { GET_SUBMISSION_REVIEW } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"
import { useRoute, useRouter } from "vue-router"

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

const initComments = "comments" in route.query
const initType =
  route.query.comments === "INLINE" || route.query.comments === "OVERALL"
    ? route.query.comments
    : null
const include_comments = ref(initComments)
const comment_type = ref(initType)
const filter_participants = ref("participants" in route.query)
const export_participants = ref([])
const downloadBlob = ref("")

function buildQuery() {
  const query: Record<string, string> = {}
  if (include_comments.value) {
    query.comments = comment_type.value ?? ""
  }
  if (filter_participants.value) {
    query.participants = `[${export_participants.value.map((u) => u.id).join(",")}]`
  }
  return query
}

const showPreview = computed({
  get: () => route.query.preview === "1",
  set: (val) => {
    if (val) {
      router.push({ query: { ...buildQuery(), preview: "1" } })
    } else if (route.query.preview) {
      router.back()
    }
  }
})

const exportOptions = computed(() => {
  const emptyFilter =
    filter_participants.value && !export_participants.value.length
  return {
    includeInline:
      include_comments.value &&
      comment_type.value !== "OVERALL" &&
      !emptyFilter,
    includeOverall:
      include_comments.value && comment_type.value !== "INLINE" && !emptyFilter,
    createdBy: filter_participants.value
      ? export_participants.value.map((u) => u.id)
      : []
  }
})

interface Props {
  id: string
}
const props = defineProps<Props>()

const commentVars = computed(() => ({
  id: props.id
}))
const { result } = useQuery(GET_SUBMISSION_REVIEW, commentVars)
const submission = computed(() => result.value?.submission)

const inline_comments_count = computed(() => getCommentCount("inline_comments"))
const overall_comments_count = computed(() =>
  getCommentCount("overall_comments")
)

const comment_options = computed(() => [
  {
    label: t(
      `export.comments.inline_and_overall`,
      inline_comments_count.value + overall_comments_count.value
    ),
    value: null
  },
  {
    label: t(`export.comments.inline_only`, inline_comments_count.value),
    value: "INLINE"
  },
  {
    label: t(`export.comments.overall_only`, overall_comments_count.value),
    value: "OVERALL"
  }
])

const downloadFilename = computed(() => {
  const base = `submission_${props.id}`
  return include_comments.value ? `${base}_comments.html` : `${base}.html`
})

function getCommentCount(type) {
  const comments = submission.value?.[type] ?? []
  return comments.reduce((sum, c) => sum + 1 + c.replies.length, 0)
}
</script>

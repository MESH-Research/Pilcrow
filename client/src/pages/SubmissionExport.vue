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
          :label="$t(`export.preview`)"
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
      @update:blob="downloadBlob = $event"
    />
  </div>
</template>
<script setup>
import ExportParticipantSelector from "../components/atoms/ExportParticipantSelector.vue"
import SubmissionExportGenerator from "../components/SubmissionExportGenerator.vue"
import { computed, ref } from "vue"
import { GET_SUBMISSION_REVIEW } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"

const { t } = useI18n()
const include_comments = ref(false)
const comment_type = ref(null)
const filter_participants = ref(false)
const export_participants = ref([])
const downloadBlob = ref("")

const showPreview = ref(false)

const exportOptions = computed(() => ({
  includeInline: include_comments.value && comment_type.value !== "OVERALL",
  includeOverall: include_comments.value && comment_type.value !== "INLINE",
  createdBy: filter_participants.value
    ? export_participants.value.map((u) => u.id)
    : []
}))

const props = defineProps({
  id: {
    type: String,
    required: true
  }
})

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

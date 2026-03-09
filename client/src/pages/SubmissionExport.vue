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
            <p>{{ $t("export.comments.byline") }}</p>
            <q-option-group
              v-model="export_option_choice"
              :options="export_options"
              type="radio"
            />
          </q-card-section>
        </q-card>
        <export-participant-selector
          v-model="export_participants"
          :submission-id="id"
          :commenter-type="export_option_choice"
        />
      </section>
      <div v-if="submission" class="row q-gutter-md q-py-md">
        <q-btn
          :label="$t(`export.submission`)"
          color="accent"
          icon="file_download"
          :href="blob"
          :download="`submission_${submission.id}.html`"
        />
        <q-btn
          :label="$t(`export.review_comments`)"
          color="primary"
          icon="chat_bubble"
          :to="{
            name: 'submission:comments',
            params: { id: submission.id },
            query: {
              export: export_option_choice,
              ids: export_participants.map((user) => user.id).join(',')
            }
          }"
        />
      </div>
      <q-spinner v-else />
    </article>
  </div>
</template>
<script setup>
import ExportParticipantSelector from "../components/atoms/ExportParticipantSelector.vue"
import { computed, ref } from "vue"
import { GET_SUBMISSION_REVIEW } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"

const { t } = useI18n()
const export_option_choice = ref(null)
const export_participants = ref([])

const props = defineProps({
  id: {
    type: String,
    required: true
  }
})

const commentVars = computed(() => ({
  id: props.id,
  createdBy: export_participants.value.map((u) => u.id)
}))
const { result } = useQuery(GET_SUBMISSION_REVIEW, commentVars)
const submission = computed(() => result.value?.submission)

const inline_comments_count = computed(() => getCommentCount("inline_comments"))
const overall_comments_count = computed(() =>
  getCommentCount("overall_comments")
)

const export_options = computed(() => [
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

function getCommentCount(type) {
  let reply_count = 0
  if (!submission.value?.[`${type}`]) {
    return reply_count
  }
  submission.value?.[`${type}`].map((comment) => {
    reply_count += comment.replies.length
  })
  return submission.value?.[`${type}`].length + reply_count ?? 0
}

const blob = computed(() =>
  URL.createObjectURL(
    new Blob([submission.value.content.data], { type: "text/html" })
  )
)
</script>

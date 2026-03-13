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
      <div class="row q-gutter-md q-py-md">
        <q-btn
          :label="$t(`export.download.title`)"
          color="accent"
          icon="file_download"
          :to="exportHtmlRoute"
        />
        <q-btn
          :label="$t(`export.preview.action`)"
          color="primary"
          icon="visibility"
          :to="exportHtmlRoute"
        />
      </div>
    </article>
  </div>
</template>
<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetExportOptions($id: ID!) {
    submission(id: $id) {
      id
      title
      inline_comments {
        id
        replies {
          id
        }
      }
      overall_comments {
        id
        replies {
          id
        }
      }
    }
  }
`)
</script>

<script setup lang="ts">
import ExportParticipantSelector from "../components/atoms/ExportParticipantSelector.vue"
import { computed, ref } from "vue"
import { GetExportOptionsDocument } from "src/graphql/generated/graphql"
import { useQuery } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"

const { t } = useI18n()

const include_comments = ref(false)
const comment_type = ref<string | null>(null)
const filter_participants = ref(false)
const export_participants = ref<{ id: string }[]>([])

interface Props {
  id: string
}

const props = defineProps<Props>()

const { result } = useQuery(GetExportOptionsDocument, () => ({
  id: props.id
}))
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

const exportHtmlRoute = computed(() => {
  const query: Record<string, string> = {}
  if (include_comments.value && comment_type.value) {
    query.comments = comment_type.value
  }
  if (
    include_comments.value &&
    filter_participants.value &&
    export_participants.value.length
  ) {
    query.createdBy = export_participants.value.map((u) => u.id).join(",")
  }
  return {
    name: "submission:export:html",
    params: { id: props.id },
    query
  }
})

function getCommentCount(type: "inline_comments" | "overall_comments") {
  const comments = submission.value?.[type] ?? []
  return comments.reduce((sum, c) => sum + 1 + (c.replies?.length ?? 0), 0)
}
</script>

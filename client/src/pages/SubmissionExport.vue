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
              v-if="include_comments && filter_participants && submission"
              v-model="export_participants"
              :submission="submission"
            />
          </q-card-section>
        </q-card>
      </section>
      <div class="row q-gutter-md q-py-md">
        <q-btn
          :label="$t(`export.download.title`)"
          color="accent"
          icon="file_download"
          @click="navigateTo(downloadRoute)"
        />
        <q-btn
          :label="$t(`export.preview.action`)"
          color="primary"
          icon="visibility"
          @click="navigateTo(exportHtmlRoute)"
        />
      </div>
    </article>
  </div>
</template>
<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  query GetExportOptions($id: ID!, $commenterType: CommentParticipantType) {
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
      ...exportParticipantSelector
    }
  }
`)
</script>

<script setup lang="ts">
import ExportParticipantSelector from "../components/atoms/ExportParticipantSelector.vue"
import { computed, ref } from "vue"
import type { CommentParticipantType } from "src/graphql/generated/graphql"
import { GetExportOptionsDocument } from "src/graphql/generated/graphql"
import { useQuery } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"
import { useRoute, useRouter } from "vue-router"
import type { RouteLocationRaw } from "vue-router"

const { t } = useI18n()
const route = useRoute()
const router = useRouter()

interface Props {
  id: string
}

const props = defineProps<Props>()

function initCommentsParam(): CommentParticipantType | null {
  const val = route.query.comments
  if (val === "INLINE" || val === "OVERALL")
    return val as CommentParticipantType
  return null
}

function initCreatedByParam(): { id: string }[] {
  const val = route.query.commentsCreatedBy
  if (typeof val !== "string") return []
  const inner = val.replace(/^\[|\]$/g, "")
  return inner
    ? inner
        .split(",")
        .filter(Boolean)
        .map((id) => ({ id }))
    : []
}

const include_comments = ref(route.query.comments != null)
const comment_type = ref<CommentParticipantType | null>(initCommentsParam())
const filter_participants = ref(route.query.commentsCreatedBy != null)
const export_participants = ref<{ id: string }[]>(initCreatedByParam())

const { result } = useQuery(GetExportOptionsDocument, () => ({
  id: props.id,
  commenterType: comment_type.value
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

function buildExportQuery() {
  const query: Record<string, string> = {}
  if (include_comments.value) {
    query.comments = comment_type.value ?? "ALL"
  }
  if (include_comments.value && filter_participants.value) {
    query.commentsCreatedBy = `[${export_participants.value.map((u) => u.id).join(",")}]`
  }
  return query
}

const exportHtmlRoute = computed(() => ({
  name: "submission:export:html",
  params: { id: props.id },
  query: buildExportQuery()
}))

const downloadRoute = computed(() => ({
  name: "submission:export:html",
  params: { id: props.id },
  query: { ...buildExportQuery(), action: "download" }
}))

async function navigateTo(target: RouteLocationRaw) {
  await router.replace({
    name: "submission:export",
    params: { id: props.id },
    query: buildExportQuery()
  })
  router.push(target)
}

function getCommentCount(type: "inline_comments" | "overall_comments") {
  const comments = submission.value?.[type] ?? []
  return comments.reduce((sum, c) => sum + 1 + (c.replies?.length ?? 0), 0)
}
</script>

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
        <q-card square bordered flat>
          <q-card-section>
            <h4 class="q-my-none text-bold">
              {{ $t("export.participants.title") }}
            </h4>
            <div v-if="all_commenters.length == 0">
              <p>{{ $t("export.participants.no_commenters") }}</p>
            </div>
            <div v-else>
              <p>{{ $t("export.participants.byline") }}</p>
              <q-list>
                <q-item
                  v-for="commenter in all_commenters"
                  :key="commenter.id"
                  tag="label"
                >
                  <q-item-section avatar>
                    <q-checkbox
                      v-model="export_participants"
                      :aria-labelledby="`commenter_label_${commenter.id}`"
                      :val="commenter"
                    />
                  </q-item-section>
                  <q-item-section top avatar>
                    <avatar-image :user="commenter" rounded />
                  </q-item-section>
                  <q-item-section>
                    <q-item-label :id="`commenter_label_${commenter.id}`">{{
                      commenter.display_label
                    }}</q-item-label>
                  </q-item-section>
                </q-item>
              </q-list>
            </div>
          </q-card-section>
        </q-card>
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
              ids: export_participants.map((user) => {
                return user.id
              })
            }
          }"
        />
      </div>
      <q-spinner v-else />
    </article>
  </div>
</template>
<script setup>
import AvatarImage from "../components/atoms/AvatarImage.vue"
import { computed, ref, watch, onMounted } from "vue"
import { GET_SUBMISSION_REVIEW } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import { useI18n } from "vue-i18n"

const { t } = useI18n()
const export_option_choice = ref(null)
const export_participants = ref([])

onMounted(() => {
  export_option_choice.value = "io"
})

const submission = computed(() => {
  return result.value?.submission
})

const props = defineProps({
  id: {
    type: String,
    required: true
  }
})

const { result } = useQuery(GET_SUBMISSION_REVIEW, { id: props.id })
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
    value: "io"
  },
  {
    label: t(`export.comments.inline_only`, inline_comments_count.value),
    value: "i"
  },
  {
    label: t(`export.comments.overall_only`, overall_comments_count.value),
    value: "o"
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

function getCommenters(type) {
  const replies = []
  if (!submission.value?.[`${type}`]) {
    return replies
  }
  const comments = submission.value?.[`${type}`].map((comment) => {
    comment.replies.map((reply) => {
      replies.push(reply.created_by)
    })
    return comment.created_by
  })

  return [...new Set([...new Set(comments), ...new Set(replies)])]
}

const inline_commenters = computed(() => getCommenters("inline_comments"))
const overall_commenters = computed(() => getCommenters("overall_comments"))

const all_commenters = computed(() => {
  let commenters = inline_commenters.value.concat(
    overall_commenters.value.filter(
      (item2) => !inline_commenters.value.some((item1) => item1.id === item2.id)
    )
  )
  if (export_option_choice.value === "i") {
    commenters = inline_commenters.value
  }
  if (export_option_choice.value === "o") {
    commenters = overall_commenters.value
  }
  return commenters
})

watch([result, all_commenters], () => {
  export_participants.value = all_commenters.value
})

const blob = computed(() =>
  URL.createObjectURL(
    new Blob([submission.value.content.data], { type: "text/html" })
  )
)
</script>

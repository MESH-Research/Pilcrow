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
            <h4 class="q-mt-none">Comments</h4>
            <p>Which comment types should be included?</p>
            <q-option-group
              v-model="export_option_choice"
              :options="export_options"
              type="radio"
            />
          </q-card-section>
        </q-card>
        <q-card square bordered flat>
          <q-card-section>
            <h4 class="q-mt-none">Participants</h4>
            <p>Who should be included?</p>
            <q-list>
              <q-item
                v-for="commenter in all_commenters"
                :key="commenter.id"
                tag="label"
              >
                <q-item-section avatar>
                  <q-checkbox
                    v-model="export_participants[commenter.id]"
                    :val="commenter.id"
                  />
                </q-item-section>
                <q-item-section>
                  <q-item-label
                    >{{ commenter.id }}
                    {{ commenter.display_label }}</q-item-label
                  >
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </q-card>
      </section>
      <q-btn
        v-if="submission"
        class="q-mt-lg"
        :label="$t(`export.download.title`)"
        color="accent"
        icon="file_download"
        :href="blob"
        :download="`submission_${submission.id}.html`"
      />
      <q-spinner v-else />
    </article>
  </div>
</template>
<script setup>
import { computed, ref } from "vue"
import { GET_SUBMISSION_REVIEW } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
const export_option_choice = ref("io")
const export_participants = ref([])
const export_options = [
  { label: "Inline and Overall Comments", value: "io" },
  { label: "Inline Comments Only", value: "i" },
  { label: "Overall Comments Only", value: "o" }
]
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
const inline_commenters = computed(() => {
  const i = submission.value?.inline_comments.map(
    (comment) => comment.created_by
  )
  return [...new Set(i)]
})
const overall_commenters = computed(() => {
  const o = submission.value?.overall_comments.map(
    (comment) => comment.created_by
  )
  return [...new Set(o)]
})
const all_commenters = computed(() => {
  let c = [...inline_commenters.value, ...overall_commenters.value]
  if (export_option_choice.value === "i") {
    c = inline_commenters.value
  }
  if (export_option_choice.value === "o") {
    c = overall_commenters.value
  }
  console.log(c)
  return c
})
const blob = computed(() =>
  URL.createObjectURL(
    new Blob([submission.value.content.data], { type: "text/html" })
  )
)
</script>

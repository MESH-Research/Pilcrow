<template>
  <q-card flat bordered class="ror-card column">
    <q-card-section class="row items-start no-wrap q-gutter-sm q-py-sm q-px-md">
      <q-checkbox
        v-model="selectedModel"
        :aria-label="checkboxLabel"
        :title="checkboxLabel"
      />
      <div class="col" style="min-width: 0">
        <div class="text-caption text-grey-7">
          #{{ assignment.submission.id }}
        </div>
        <router-link
          :to="{
            name: 'submission:view',
            params: { id: assignment.submission.id }
          }"
          class="text-primary submission-title"
          style="font-size: 1.125rem; line-height: 1.3"
          :title="assignment.submission.title"
        >
          {{ assignment.submission.title }}
        </router-link>
      </div>
    </q-card-section>
    <q-separator />
    <q-card-section class="q-py-sm q-px-md">
      <div class="text-caption text-weight-bold text-grey-7 q-mb-xs">
        {{ $t("submission_tables.columns.publication") }}
      </div>
      {{ assignment.submission.publication.name }}
    </q-card-section>
    <q-separator />
    <q-card-section class="row no-wrap q-py-sm q-px-md q-gutter-md">
      <div class="col">
        <div class="text-caption text-weight-bold text-grey-7 q-mb-xs">
          {{ $t("submission_tables.columns.role") }}
        </div>
        {{ $t(`admin.users.details.roles.${assignment.role}`) }}
      </div>
      <div class="col">
        <div class="text-caption text-weight-bold text-grey-7 q-mb-xs">
          {{ $t("submission_tables.columns.status") }}
        </div>
        {{ $t(`submission.status.${assignment.submission.status}`) }}
      </div>
    </q-card-section>
    <q-separator />
    <q-card-section class="q-py-sm q-px-md">
      <div class="text-caption text-weight-bold text-grey-7 q-mb-xs">
        {{ $t("submission_tables.columns.updated_at") }}
      </div>
      <div>{{ updatedAbsolute }}</div>
      <div class="text-caption text-grey-7">{{ updatedRelative }}</div>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { computed } from "vue"
import { useI18n } from "vue-i18n"
import { DateTime } from "luxon"
import { useTimeAgo } from "src/use/timeAgo"

interface Submitter {
  id: string
  name?: string | null
  username?: string | null
}

interface Submission {
  id: string
  title: string
  status: string
  updated_at: string
  submitters?: Submitter[]
  publication: { id: string; name: string }
}

interface Assignment {
  id: string
  role: string
  submission: Submission
}

interface Props {
  assignment: Assignment
}

const props = defineProps<Props>()
const selectedModel = defineModel<boolean>("selected", { default: false })

const { t } = useI18n()
const timeAgo = useTimeAgo()

const checkboxLabel = computed(() =>
  t("record_of_review.label_select_review_checkbox", {
    id: props.assignment.submission.id,
    title: props.assignment.submission.title
  })
)

const updatedDate = computed(() =>
  DateTime.fromISO(props.assignment.submission.updated_at)
)
const updatedAbsolute = computed(() =>
  updatedDate.value.toFormat("LLL d yyyy h:mm a")
)
const updatedRelative = computed(() =>
  timeAgo.format(updatedDate.value.toJSDate(), "long")
)
</script>

<style lang="sass" scoped>
.ror-card
  height: 100%
  .submission-title
    text-decoration: none
    &:hover
      text-decoration: underline
</style>

<template>
  <q-card flat bordered class="submission-card column">
    <!-- Header: title on top; submitter on the left, status on the right -->
    <q-card-section class="q-py-sm q-px-md">
      <router-link
        :to="{
          name: 'submission:details',
          params: { id: submission.id }
        }"
        class="text-primary submission-title block q-mb-sm"
        style="font-size: 1.25rem; line-height: 1.3"
      >
        {{ submission.title }}
      </router-link>
      <div :class="['items-start q-gutter-sm', stackHeader ? 'column' : 'row']">
        <q-item
          v-if="submission.created_by"
          class="q-pa-none submitter-item"
          :class="stackHeader ? '' : 'col'"
        >
          <q-item-section side>
            <avatar-image :user="submission.created_by" size="40px" rounded />
          </q-item-section>
          <q-item-section style="min-width: 0">
            <q-item-label class="ellipsis">
              {{ submission.created_by.name }}
            </q-item-label>
            <q-item-label
              v-if="submission.created_by.username"
              caption
              class="ellipsis"
            >
              {{ submission.created_by.username }}
            </q-item-label>
          </q-item-section>
        </q-item>
        <q-space v-else-if="!stackHeader" />
        <div
          :class="[
            'q-pa-sm rounded-borders',
            `bg-${statusStyle.color}`,
            statusStyle.textClass,
            statusStyle.pattern
          ]"
        >
          <div
            class="row items-center no-wrap pattern-text-mask"
            :class="canChangeStatus ? 'cursor-pointer' : ''"
            :role="canChangeStatus ? 'button' : undefined"
            :tabindex="canChangeStatus ? 0 : undefined"
            :aria-label="
              canChangeStatus
                ? $t('submissions.action.change_status.label') +
                  ': ' +
                  statusLabel
                : statusLabel
            "
            :aria-haspopup="canChangeStatus ? 'menu' : undefined"
            @click.stop
            @keydown.enter.stop
            @keydown.space.stop
          >
            <q-icon :name="statusStyle.icon" size="sm" class="q-mr-sm" />
            <span class="text-weight-medium" style="font-size: 0.9rem">
              {{ statusLabel }}
            </span>
            <q-icon
              v-if="canChangeStatus"
              name="arrow_drop_down"
              size="sm"
              class="q-ml-xs"
            />
            <q-menu
              v-if="canChangeStatus"
              anchor="bottom start"
              self="top start"
            >
              <q-list dense style="min-width: 220px">
                <q-item
                  v-for="transition in transitions"
                  :key="transition.action"
                  v-close-popup
                  role="menuitem"
                  clickable
                  @click.stop="openConfirm(transition.action)"
                >
                  <q-item-section>
                    {{ $t(`submission.action.${transition.action}`) }}
                  </q-item-section>
                </q-item>
              </q-list>
            </q-menu>
          </div>
        </div>
      </div>
    </q-card-section>

    <q-separator />

    <!-- Review Coordinator -->
    <q-card-section class="q-py-sm q-px-md q-pb-none">
      <div class="text-caption text-weight-bold text-grey-7">
        {{ $t("publication.dashboard.headers.review_coordinators") }}
      </div>
    </q-card-section>
    <q-card-section class="q-py-sm q-px-md">
      <q-item class="q-pa-none">
        <q-item-section side>
          <avatar-image
            v-if="coordinator"
            :user="coordinator"
            size="40px"
            rounded
          />
          <q-avatar
            v-else
            size="40px"
            color="grey-4"
            text-color="grey-7"
            icon="person_off"
          />
        </q-item-section>
        <q-item-section>
          <q-item-label>
            {{ coordinator?.name ?? $t("review_team.no_coordinator") }}
          </q-item-label>
          <q-item-label v-if="coordinator?.username" caption>
            {{ coordinator.username }}
          </q-item-label>
        </q-item-section>
      </q-item>
    </q-card-section>

    <!-- Reviewers -->
    <q-card-section
      v-if="(submission.reviewers ?? []).length"
      class="q-py-sm q-px-md"
    >
      <div class="text-caption text-weight-bold text-grey-7 q-mb-xs">
        {{ $t("publication.dashboard.headers.reviewers") }}
      </div>
      <div class="row q-col-gutter-sm">
        <div
          v-for="r in submission.reviewers"
          :key="r.id"
          class="col-12 col-md-6"
        >
          <q-item class="q-pa-none">
            <q-item-section side>
              <avatar-image :user="r" size="40px" rounded />
            </q-item-section>
            <q-item-section>
              <q-item-label>{{ r.name ?? r.email }}</q-item-label>
              <q-item-label v-if="r.username" caption>
                {{ r.username }}
              </q-item-label>
            </q-item-section>
          </q-item>
        </div>
      </div>
    </q-card-section>

    <!-- Footer: pushed to bottom via spacer -->
    <q-space />
    <q-separator />
    <q-card-section class="q-py-xs q-px-md text-caption text-grey-7">
      <div class="row q-col-gutter-md">
        <div class="col">
          <div>
            <q-icon name="update" size="xs" class="q-mr-xs" />
            <span :title="$t('publication.dashboard.headers.updated_at')">
              {{ absoluteUpdated }}
            </span>
          </div>
          <div class="text-grey-5">{{ relativeUpdated }}</div>
        </div>
        <div v-if="submission.submitted_at" class="col">
          <div>
            <q-icon name="upload" size="xs" class="q-mr-xs" />
            <span :title="$t('publication.dashboard.headers.submitted_at')">
              {{ absoluteSubmitted }}
            </span>
          </div>
          <div class="text-grey-5">{{ relativeSubmitted }}</div>
        </div>
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import { computed } from "vue"
import { useI18n } from "vue-i18n"
import { useQuasar } from "quasar"
import { DateTime } from "luxon"
import { useTimeAgo } from "src/use/timeAgo"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import ConfirmStatusChangeDialog from "src/components/dialogs/ConfirmStatusChangeDialog.vue"
import type { Submission } from "src/graphql/generated/graphql"
import { statusStyleMap } from "./statusCategories"
import { useStatusTransitions } from "src/use/submissionStatusTransitions"

interface CardUser {
  id?: string
  name?: string | null
  email: string
  username?: string | null
}

interface SubmissionRow {
  id: string
  title: string
  status: string
  updated_at: string
  submitted_at?: string | null
  created_by?: CardUser
  review_coordinators?: CardUser[]
  reviewers?: CardUser[]
}

interface Props {
  submission: SubmissionRow
}

const props = defineProps<Props>()
const { t } = useI18n()
const $q = useQuasar()
const { dialog } = $q
const timeAgo = useTimeAgo()

// At smaller viewport widths cards are narrow (especially in the 2-col
// grid range); stack the submitter and status vertically rather than
// letting them overflow side-by-side.
const stackHeader = computed(() => $q.screen.lt.md)

const statusStyle = computed(
  () =>
    statusStyleMap[props.submission.status] ?? {
      color: "grey",
      textClass: "text-white",
      icon: "help",
      pattern: ""
    }
)

const statusLabel = computed(() =>
  t(`submission.status.${props.submission.status}`)
)

const { canChangeStatus, transitions } = useStatusTransitions(
  computed(() => props.submission as unknown as Submission)
)

function openConfirm(action: string) {
  dialog({
    component: ConfirmStatusChangeDialog,
    componentProps: {
      action,
      submissionId: props.submission.id,
      currentStatus: props.submission.status
    }
  })
}

const coordinator = computed(
  () => (props.submission.review_coordinators ?? [])[0] ?? null
)

const updatedDt = computed(() => DateTime.fromISO(props.submission.updated_at))
const absoluteUpdated = computed(() =>
  updatedDt.value.toFormat("LLL d yyyy h:mm a")
)
const relativeUpdated = computed(() =>
  timeAgo.format(updatedDt.value.toJSDate(), "long")
)
const submittedDt = computed(() =>
  props.submission.submitted_at
    ? DateTime.fromISO(props.submission.submitted_at)
    : null
)
const absoluteSubmitted = computed(
  () => submittedDt.value?.toFormat("LLL d yyyy h:mm a") ?? ""
)
const relativeSubmitted = computed(() =>
  submittedDt.value ? timeAgo.format(submittedDt.value.toJSDate(), "long") : ""
)
</script>

<style scoped>
.submission-card .submission-title {
  text-decoration: none;
  font-weight: 500;
}
.submission-card .submission-title:hover {
  text-decoration: underline;
}
:deep(.q-item__label + .q-item__label) {
  margin-top: 0;
}
:deep(.q-item__label) {
  line-height: 1.25;
}
</style>

<!-- Dark-mode contrast fixes: the hardcoded grey-5/grey-7 on section
     headers and timestamps render near-black against the dark grid
     background. Shift them to a lighter grey when body--dark is set.
     Scoped styles can't target the body class ancestor, so this
     override sits in an unscoped block. -->
<style>
.body--dark .submission-card .text-grey-7 {
  color: #d0d0d0;
}
.body--dark .submission-card .text-grey-5 {
  color: #9e9e9e;
}
</style>

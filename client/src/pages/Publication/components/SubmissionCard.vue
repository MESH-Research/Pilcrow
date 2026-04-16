<template>
  <q-card flat bordered class="submission-card column">
    <!-- Header: title + status in two columns -->
    <q-card-section class="q-py-sm q-px-md">
      <div class="row q-col-gutter-sm items-center">
        <div class="col-12 col-sm">
          <router-link
            :to="{
              name: 'submission:details',
              params: { id: submission.id }
            }"
            class="text-primary submission-title"
            style="font-size: 1.25rem; line-height: 1.3"
          >
            {{ submission.title }}
          </router-link>
        </div>
        <div class="col-12 col-sm-auto">
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
      </div>
    </q-card-section>

    <!-- Submitter -->
    <q-card-section v-if="submission.created_by" class="q-py-sm q-px-md">
      <q-item class="q-pa-none">
        <q-item-section side>
          <avatar-image :user="submission.created_by" size="md" rounded />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ submission.created_by.name }}</q-item-label>
          <q-item-label caption>
            {{ $t("publication.dashboard.headers.created_by") }}
          </q-item-label>
        </q-item-section>
      </q-item>
    </q-card-section>

    <q-separator />

    <!-- Review Coordinator -->
    <q-card-section
      v-if="(submission.review_coordinators ?? []).length"
      class="q-py-sm q-px-md"
    >
      <q-item class="q-pa-none">
        <q-item-section side>
          <avatar-image
            :user="submission.review_coordinators[0]"
            size="md"
            rounded
          />
        </q-item-section>
        <q-item-section>
          <q-item-label>
            {{ submission.review_coordinators[0].name }}
          </q-item-label>
          <q-item-label caption>
            {{ $t("publication.dashboard.headers.review_coordinators") }}
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
      <q-list dense class="q-pa-none">
        <q-item
          v-for="r in submission.reviewers"
          :key="r.id"
          class="q-pa-none q-mb-xs"
        >
          <q-item-section side>
            <avatar-image :user="r" size="md" rounded />
          </q-item-section>
          <q-item-section>
            <q-item-label>{{ r.name ?? r.email }}</q-item-label>
          </q-item-section>
        </q-item>
      </q-list>
    </q-card-section>

    <!-- Footer: pushed to bottom via spacer -->
    <q-space />
    <q-separator />
    <q-card-section class="q-py-xs q-px-md text-caption text-grey-7">
      <div>
        <q-icon name="update" size="xs" class="q-mr-xs" />
        {{ absoluteDate }}
      </div>
      <div class="text-grey-5">{{ relativeDate }}</div>
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

interface SubmissionRow {
  id: string
  title: string
  status: string
  updated_at: string
  created_by?: { name?: string | null; email: string }
  review_coordinators?: { id: string; name?: string | null; email: string }[]
  reviewers?: { id: string; name?: string | null; email: string }[]
}

interface Props {
  submission: SubmissionRow
}

const props = defineProps<Props>()
const { t } = useI18n()
const { dialog } = useQuasar()
const timeAgo = useTimeAgo()

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

const dt = computed(() => DateTime.fromISO(props.submission.updated_at))
const absoluteDate = computed(() => dt.value.toFormat("LLL d yyyy h:mm a"))
const relativeDate = computed(() => timeAgo.format(dt.value.toJSDate(), "long"))
</script>

<style scoped>
.submission-card .submission-title {
  text-decoration: none;
  font-weight: 500;
}
.submission-card .submission-title:hover {
  text-decoration: underline;
}
</style>

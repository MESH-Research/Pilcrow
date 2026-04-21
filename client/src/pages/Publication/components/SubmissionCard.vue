<template>
  <q-card flat bordered class="submission-card column">
    <!-- Header: a thin full-width color/pattern strip carries the
         category, separator, then the title section, separator
         again. The icon is left-aligned with a vertical separator
         right after it — that tiny gutter makes the icon read as
         a deliberate tag rather than floating text. -->
    <q-card-section
      :class="[
        'category-band q-py-xs q-pl-md q-pr-none',
        `bg-${statusStyle.color}`,
        statusStyle.textClass,
        statusStyle.pattern
      ]"
      :aria-label="categoryLabel"
    >
      <q-icon :name="statusStyle.icon" size="sm" class="pattern-text-mask" />
      <q-separator vertical class="q-ml-sm category-band-sep" color="white" />
    </q-card-section>
    <q-separator />
    <q-card-section class="q-py-sm q-px-md">
      <div class="row items-start no-wrap q-gutter-sm">
        <div class="col" style="min-width: 0">
          <div class="text-caption text-grey-7 submission-id">
            #{{ submission.id }}
          </div>
          <router-link
            :to="{
              name: 'submission:details',
              params: { id: submission.id }
            }"
            class="text-primary submission-title"
            style="font-size: 1.25rem; line-height: 1.3"
            :title="submission.title"
          >
            {{ submission.title }}
          </router-link>
        </div>
        <div
          class="status-chip row items-center no-wrap q-px-sm q-py-xs"
          :class="[canChangeStatus ? 'cursor-pointer' : '']"
          :style="`border-color: var(--q-${statusStyle.color})`"
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
          <span
            :class="['status-dot', `bg-${statusStyle.color}`]"
            aria-hidden="true"
          />
          <span class="text-weight-medium q-ml-xs" style="font-size: 0.9rem">
            {{ statusLabel }}
          </span>
          <q-icon
            v-if="canChangeStatus"
            name="arrow_drop_down"
            size="sm"
            class="q-ml-xs"
          />
          <q-menu v-if="canChangeStatus" anchor="bottom start" self="top start">
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
    </q-card-section>
    <q-separator />

    <!-- Submitter(s): the submission's author(s). Modeled as a list
         because we're expected to grow into co-submitters later. -->
    <q-card-section v-if="submission.created_by" class="q-py-sm q-px-md">
      <div class="text-caption text-weight-bold text-grey-7 q-mb-sm">
        {{ $t("publication.dashboard.headers.created_by") }}
      </div>
      <q-item
        class="q-pa-none submitter-item"
        :class="submitterLink ? 'user-item-link' : ''"
        :clickable="!!submitterLink"
        :to="submitterLink || undefined"
        @click.stop
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
    </q-card-section>

    <q-separator />

    <!-- Review Team: coordinator (with RC badge) plus reviewers -->
    <q-card-section class="q-py-sm q-px-md">
      <div class="text-caption text-weight-bold text-grey-7 q-mb-sm">
        {{ $t("publication.dashboard.headers.review_team") }}
      </div>
      <q-item
        class="q-pa-none q-mb-sm"
        :class="coordinatorLink ? 'user-item-link' : ''"
        :clickable="!!coordinatorLink"
        :to="coordinatorLink || undefined"
        @click.stop
      >
        <q-item-section side>
          <div class="relative-position">
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
            <q-badge
              floating
              :color="coordinator ? 'primary' : 'grey-6'"
              class="rc-badge"
              :aria-label="
                $t('publication.dashboard.headers.review_coordinators')
              "
            >
              RC
            </q-badge>
          </div>
        </q-item-section>
        <q-item-section style="min-width: 0">
          <q-item-label class="ellipsis">
            {{ coordinator?.name ?? $t("review_team.no_coordinator") }}
          </q-item-label>
          <q-item-label v-if="coordinator?.username" caption class="ellipsis">
            {{ coordinator.username }}
          </q-item-label>
        </q-item-section>
      </q-item>
      <div
        v-if="(submission.reviewers ?? []).length"
        class="row q-col-gutter-sm"
      >
        <div
          v-for="r in submission.reviewers"
          :key="r.id"
          class="col-12 col-md-6"
        >
          <q-item
            class="q-pa-none"
            :class="teamMemberLinkTo(r.id) ? 'user-item-link' : ''"
            :clickable="!!teamMemberLinkTo(r.id)"
            :to="teamMemberLinkTo(r.id) || undefined"
            @click.stop
          >
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
import { useRoute } from "vue-router"
import { DateTime } from "luxon"
import { useTimeAgo } from "src/use/timeAgo"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import ConfirmStatusChangeDialog from "src/components/dialogs/ConfirmStatusChangeDialog.vue"
import type { RouteLocationRaw } from "vue-router"
import type { Submission } from "src/graphql/generated/graphql"
import { statusCategories, statusStyleMap } from "./statusCategories"
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
const route = useRoute()

// The card lives under /manage/publication/:id/... — the active
// route's `id` param is the publication we're scoped to. Used to
// build links back to per-user detail pages.
const publicationId = computed(() => {
  const raw = (route.params as Record<string, string | string[] | undefined>).id
  return Array.isArray(raw) ? raw[0] : raw
})

function submitterLinkTo(
  userId: string | undefined | null
): RouteLocationRaw | null {
  if (!userId || !publicationId.value) return null
  return {
    name: "manage:publication:submitter" as const,
    params: { id: publicationId.value, userId }
  }
}

function teamMemberLinkTo(
  userId: string | undefined | null
): RouteLocationRaw | null {
  if (!userId || !publicationId.value) return null
  return {
    name: "manage:publication:team_member" as const,
    params: { id: publicationId.value, userId }
  }
}

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

const categoryKey = computed(
  () =>
    statusCategories.find((c) => c.statuses.includes(props.submission.status))
      ?.key ?? null
)
const categoryLabel = computed(() =>
  categoryKey.value
    ? t(`publication.dashboard.categories.${categoryKey.value}`)
    : ""
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

const submitterLink = computed(() =>
  submitterLinkTo(props.submission.created_by?.id ?? null)
)
const coordinatorLink = computed(() =>
  teamMemberLinkTo(coordinator.value?.id ?? null)
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
/* Clickable q-item rows that navigate to a user detail page.
   We lean on Quasar's built-in `:to` + `clickable` behavior so
   the flex layout and ellipsis truncation in the item sections
   stay intact. The default q-focus-helper tints with the primary
   color (blue) on hover/focus, which reads as an accidental
   highlight here — suppress it and provide our own neutral fill. */
.user-item-link {
  border-radius: 4px;
  /* Equal left padding to the accent bar we add when this row is the
     currently-viewed user — keeps non-active rows from shifting when
     a sibling takes on the active style. */
  padding-left: 4px;
  border-left: 3px solid transparent;
}
.user-item-link :deep(.q-focus-helper) {
  display: none;
}
.user-item-link:hover {
  background: rgba(0, 0, 0, 0.04);
}
.body--dark .user-item-link:hover {
  background: rgba(255, 255, 255, 0.06);
}
/* When the row's :to matches the current route (i.e. we're on that
   user's detail page and they appear in this submission's team), the
   router-link--active class lights it up with the app-wide nav tint
   ($active, a bright blue) — reads more like a misfired nav
   highlight than "this is the subject you're looking at". Override
   with the warm highlight tone + a left accent so it reads as a
   "you are here" marker instead. */
.user-item-link.q-router-link--active,
.user-item-link.q-router-link--exact-active {
  background-color: #fdf2d1 !important;
  border-left-color: #d9a441 !important;
  color: inherit !important;
}
.body--dark .user-item-link.q-router-link--active,
.body--dark .user-item-link.q-router-link--exact-active {
  background-color: rgba(216, 171, 65, 0.14) !important;
  border-left-color: #d9a441 !important;
  color: inherit !important;
}

.submission-card .submission-id {
  font-size: 0.7rem;
  line-height: 1.2;
  font-variant-numeric: tabular-nums;
  margin-bottom: 2px;
}
.submission-card .submission-title {
  text-decoration: none;
  font-weight: 500;
  /* Clamp to three lines with ellipsis so cards stay roughly the
     same height. Long unbroken tokens (URL-ish strings) break
     mid-word instead of stretching the card. The full title
     lives on the anchor's `title` attribute for hover. */
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  overflow-wrap: anywhere;
  word-break: break-word;
}
.submission-card .submission-title:hover {
  text-decoration: underline;
}
/* Thin full-width strip at the top of the card carrying the
   category color + pattern + icon. The icon sits on the left
   with a subtle vertical separator after it so the icon reads
   as a deliberate tag anchoring the strip. */
.category-band {
  display: flex;
  justify-content: flex-start;
  align-items: center;
}
.category-band-sep {
  opacity: 0.45;
  align-self: stretch;
  margin-top: 2px;
  margin-bottom: 2px;
}
/* Outlined status chip. The border + dot carry the category color;
   the label text stays in the theme's default body color so we keep
   adequate contrast against the white card (some category colors,
   e.g. warning, fail WCAG AA as text on white). */
.status-chip {
  border: 1px solid;
  border-radius: 9999px;
  background: transparent;
  color: inherit;
}
.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
  flex: 0 0 auto;
}
:deep(.q-item__label + .q-item__label) {
  margin-top: 0;
}
:deep(.q-item__label) {
  line-height: 1.25;
}
.rc-badge {
  font-size: 0.7rem;
  padding: 4px 7px;
  letter-spacing: 0.02em;
  font-weight: 600;
  /* Nudge farther off the avatar corner for visual breathing room. */
  top: -6px;
  right: -5px;
  z-index: 2;
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

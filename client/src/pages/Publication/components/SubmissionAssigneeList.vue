<template>
  <section class="column q-gutter-y-sm">
    <header class="row items-center no-wrap q-gutter-x-sm">
      <h3 class="section-heading row items-center q-gutter-x-xs col q-my-none">
        <span>{{ $t(`submission.${roleGroup}.heading`) }}</span>
        <!-- RC slot is single-occupancy, so a "(1)" count just adds
             noise. A "Missing" badge when nothing's assigned is more
             informative than a count of 0. Other roles still show
             their count since the number is meaningful. -->
        <span
          v-if="roleGroup !== 'review_coordinators'"
          class="text-grey-7 text-body2"
        >
          ({{ users.length }})
        </span>
        <!-- Missing-team flag uses the dashboard's `needs_action`
             category visual identity (warning color, flag icon,
             diagonal pattern) so this row reads with the same
             "needs your attention" treatment as a status that needs
             admin action elsewhere in the manage UI. -->
        <q-chip
          v-if="!users.length"
          dense
          color="warning"
          text-color="dark"
          icon="flag"
          class="q-ml-xs pattern-diagonal"
          :label="$t('submission.assignee_list.missing_badge')"
        />
      </h3>
      <!-- Optional inline action — caller-supplied so add/replace
           CTAs share the heading row instead of stacking under
           the list. -->
      <slot name="action" />
    </header>

    <!-- Missing-RC placeholder row mirrors the ReviewTeamCell empty
         state on the submissions index, but tinted with the
         `needs_action` category color + diagonal pattern so the
         empty seat reads as a workflow alert instead of a quiet
         grey blank. Other roles keep a plain message. -->
    <q-list v-if="!users.length && roleGroup === 'review_coordinators'" dense>
      <q-item class="assignee-row assignee-row--missing">
        <q-item-section avatar>
          <q-avatar
            size="36px"
            color="warning"
            text-color="dark"
            icon="person_off"
            class="pattern-diagonal"
          />
        </q-item-section>
        <q-item-section>
          <q-item-label class="text-grey-8">
            {{ $t(`submission.${roleGroup}.none`) }}
          </q-item-label>
        </q-item-section>
      </q-item>
    </q-list>

    <p v-else-if="!users.length" class="text-grey-7 q-my-none">
      {{ $t(`submission.${roleGroup}.none`) }}
    </p>

    <q-list v-else dense separator>
      <q-item
        v-for="user in users"
        :key="user.id"
        clickable
        :to="userDetailLink(user)"
        class="assignee-row"
      >
        <q-item-section avatar>
          <!-- Corner triangle marks staged users — reads as a
               "folded-page" / dog-eared affordance signalling the
               record isn't a real account yet. Tooltip on hover
               restates the meaning for users who need text. -->
          <div class="relative-position avatar-wrap">
            <avatar-image :user="user" size="36px" rounded />
            <span
              v-if="user.staged"
              class="staged-corner-wrap"
              role="img"
              :aria-label="$t('publication.manage.user_detail.invited_aria')"
            >
              <span class="staged-corner-bg" aria-hidden="true" />
              <q-icon name="schedule" size="12px" class="staged-corner-icon" />
              <q-tooltip anchor="top middle" self="bottom middle">
                {{
                  $t(
                    "publication.manage.user_detail.invited_first_login_tooltip"
                  )
                }}
              </q-tooltip>
            </span>
          </div>
        </q-item-section>
        <q-item-section>
          <q-item-label
            class="text-weight-medium row items-center q-gutter-x-xs"
          >
            <span>{{ displayName(user) }}</span>
            <!-- Staged users are invitations the recipient hasn't
                 accepted yet — they're attached to the submission
                 by email rather than as a real account. The badge
                 mirrors the team-member detail page so the same
                 "schedule + Invited" treatment reads consistently
                 across surfaces. -->
            <q-badge
              v-if="user.staged"
              color="info"
              text-color="dark"
              class="align-middle"
              :aria-label="$t('publication.manage.user_detail.invited_aria')"
            >
              <q-icon name="schedule" size="xs" class="q-mr-xs" />
              {{ $t("publication.manage.user_detail.invited_badge") }}
              <q-tooltip anchor="top middle" self="bottom middle">
                {{
                  $t(
                    "publication.manage.user_detail.invited_first_login_tooltip"
                  )
                }}
              </q-tooltip>
            </q-badge>
          </q-item-label>
          <q-item-label v-if="assignedCaption(user)" caption>
            {{ assignedCaption(user) }}
          </q-item-label>
        </q-item-section>
        <q-item-section v-if="mutable" side>
          <q-btn
            flat
            round
            dense
            color="negative"
            icon="person_remove"
            :aria-label="
              $t(`submission.${roleGroup}.unassign_button.ariaLabel`)
            "
            :title="$t(`submission.${roleGroup}.unassign_button.help`)"
            @click.stop.prevent="emit('unassign', user.id)"
          />
        </q-item-section>
      </q-item>
    </q-list>
  </section>
</template>

<script setup lang="ts">
import { computed } from "vue"
import { DateTime } from "luxon"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import { useTimeAgo } from "src/use/timeAgo"
import { useI18n } from "vue-i18n"
import type { SubmissionAssignment, User } from "src/graphql/generated/graphql"

type RoleGroup = "submitters" | "reviewers" | "review_coordinators"

interface Props {
  users: User[]
  assignments?: SubmissionAssignment[]
  publicationId: string
  roleGroup: RoleGroup
  mutable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  assignments: () => [],
  mutable: false
})

const emit = defineEmits<{ unassign: [userId: string] }>()

const { t } = useI18n()
const timeAgo = useTimeAgo()

// Submitter detail and team-member detail are separate pages — pick
// the right one for the role we're rendering. RC and reviewer share
// the team-member detail (the underlying page handles either role).
function userDetailLink(user: User) {
  if (props.roleGroup === "submitters") {
    return {
      name: "manage:publication:submitter" as const,
      params: { id: props.publicationId, userId: user.id }
    }
  }
  return {
    name: "manage:publication:team_member" as const,
    params: { id: props.publicationId, userId: user.id }
  }
}

function displayName(user: User): string {
  return user.name || user.username || user.email
}

const roleSingular = computed(() => {
  if (props.roleGroup === "submitters") return "submitter"
  if (props.roleGroup === "reviewers") return "reviewer"
  return "review_coordinator"
})

// Map user.id → assignment so we can render "assigned 2 days ago"
// alongside each name. Filtered to the current role so a user who
// holds two roles on the same submission doesn't surface the wrong
// timestamp here.
const assignmentByUserId = computed(() => {
  const map = new Map<string, SubmissionAssignment>()
  for (const a of props.assignments) {
    if (a.role !== roleSingular.value) continue
    map.set(a.user.id, a)
  }
  return map
})

function assignedCaption(user: User): string {
  const a = assignmentByUserId.value.get(user.id)
  if (!a) return ""
  const dt = DateTime.fromISO(a.created_at)
  if (!dt.isValid) return ""
  return t("submission.assignee_list.assigned_relative", {
    relative: timeAgo.format(dt.toJSDate(), "long")
  })
}
</script>

<style scoped>
.assignee-row {
  border-radius: 4px;
}
/* Folded-corner marker for staged (invited-but-not-signed-in)
   users. The clipped background span and the unclipped icon are
   siblings so the icon sits cleanly inside the triangle's visible
   area without being chopped by the same clip-path. */
.avatar-wrap {
  display: inline-block;
}
.staged-corner-wrap {
  position: absolute;
  top: 0;
  right: 0;
  width: 24px;
  height: 24px;
  pointer-events: auto;
}
.staged-corner-bg {
  position: absolute;
  inset: 0;
  /* Info (blue) — the page already uses warning for the
     "needs your action" flag; reusing it here would conflate
     "the user needs to be acted on" with "this user record is
     still in a pending state". */
  background: var(--q-info);
  clip-path: polygon(100% 0, 0 0, 100% 100%);
  border-top-right-radius: 4px;
}
.staged-corner-icon {
  position: absolute;
  top: 1px;
  right: 1px;
  /* Quasar's `info` is a light-blue; white sits too close in
     value. Near-black reads cleanly against the lighter fill. */
  color: rgba(0, 0, 0, 0.78);
}
.body--dark .staged-corner-bg {
  filter: brightness(1.05);
}
</style>

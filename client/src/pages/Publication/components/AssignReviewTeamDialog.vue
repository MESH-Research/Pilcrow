<template>
  <q-dialog
    ref="dialogRef"
    :aria-label="dialogTitle"
    full-width
    @hide="onDialogHide"
  >
    <q-card
      class="assign-team-dialog column"
      :style="`max-width: ${multiple ? 960 : 720}px`"
    >
      <q-card-section class="row items-center q-pb-sm">
        <h3 class="section-heading col">{{ dialogTitle }}</h3>
        <q-btn flat round dense icon="close" @click="onDialogCancel" />
      </q-card-section>

      <q-separator />

      <!-- Two-column body for multi (reviewer) mode: search + pool
           on the left, selected users + shared message on the right.
           Single mode (RC) collapses to one column since at most one
           selection is possible. -->
      <div class="row col no-wrap dialog-body">
        <div class="column" :class="multiple ? 'col-7' : 'col'">
          <q-card-section class="q-pb-none">
            <q-input
              v-model="searchInput"
              outlined
              dense
              autofocus
              debounce="200"
              :placeholder="
                $t('publication.manage.assign_team.search_placeholder')
              "
            >
              <template #prepend>
                <q-icon name="search" />
              </template>
              <template v-if="searchInput" #append>
                <q-icon
                  name="close"
                  class="cursor-pointer"
                  @click="searchInput = ''"
                />
              </template>
            </q-input>

            <!-- Single-mode keeps the chip pile beneath the input;
                 multi-mode promotes the selected items to the right
                 column as a list, where they're easier to scan and
                 read. -->
            <div
              v-if="!multiple && selected.length"
              class="selected-pile q-mt-sm row items-center q-gutter-xs"
              :aria-label="$t('publication.manage.assign_team.selected_aria')"
            >
              <q-chip
                v-for="(item, idx) in selected"
                :key="chipKey(item)"
                :icon="isInviteChip(item) ? 'mail' : 'person'"
                removable
                dense
                square
                @remove="removeAt(idx)"
              >
                {{ chipLabel(item) }}
              </q-chip>
            </div>
          </q-card-section>

          <q-card-section class="col q-pt-sm scroll-area">
            <q-list separator>
              <!-- "Invite by email" pseudo-row: only when the search
                   box looks like an email and nothing in the pool
                   matches it. Reads as just another row in the list
                   so the user has one mental model: pick from this
                   list. -->
              <q-item
                v-if="inviteCandidate"
                v-ripple
                clickable
                class="invite-row"
                @click="toggleInvite(inviteCandidate)"
              >
                <q-item-section avatar>
                  <q-icon name="mail" color="accent" />
                </q-item-section>
                <q-item-section>
                  <q-item-label>
                    {{
                      $t("publication.manage.assign_team.invite_label", {
                        email: inviteCandidate
                      })
                    }}
                  </q-item-label>
                  <q-item-label caption>
                    {{ $t("publication.manage.assign_team.invite_hint") }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-icon
                    v-if="isInviteSelected(inviteCandidate)"
                    name="check_circle"
                    color="positive"
                  />
                </q-item-section>
              </q-item>

              <q-item
                v-for="entry in filteredOptions"
                :key="entry.user.id"
                v-ripple
                clickable
                :class="[isUserSelected(entry) ? 'selected-row' : '']"
                @click="toggleUser(entry)"
              >
                <q-item-section avatar>
                  <!-- Same staged-user corner marker as the
                       SubmissionAssigneeList rows so admins see the
                       "not yet signed in" flag in both the pool and
                       the already-assigned list. -->
                  <div class="relative-position avatar-wrap">
                    <avatar-image :user="entry.user" size="40px" rounded />
                    <span
                      v-if="entry.user.staged"
                      class="staged-corner-wrap"
                      role="img"
                      :aria-label="
                        $t('publication.manage.user_detail.invited_aria')
                      "
                    >
                      <span class="staged-corner-bg" aria-hidden="true" />
                      <q-icon
                        name="schedule"
                        size="12px"
                        class="staged-corner-icon"
                      />
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
                  <q-item-label class="row items-center q-gutter-x-xs">
                    <span>{{ displayName(entry.user) }}</span>
                    <q-badge
                      v-if="entry.user.staged"
                      color="info"
                      text-color="dark"
                      :aria-label="
                        $t('publication.manage.user_detail.invited_aria')
                      "
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
                  <q-item-label v-if="entry.user.email" caption>
                    {{ entry.user.email }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <div class="text-caption text-grey-8 column items-end">
                    <span class="text-weight-medium">
                      {{ activeCountLabel(entry) }}
                    </span>
                    <span v-if="otherRoleCount(entry)" class="text-grey-6">
                      {{ otherRoleLabel(entry) }}
                    </span>
                    <span v-if="lastAssignedLabel(entry)" class="text-grey-6">
                      {{ lastAssignedLabel(entry) }}
                    </span>
                  </div>
                </q-item-section>
                <q-item-section side>
                  <q-icon
                    v-if="isUserSelected(entry)"
                    name="check_circle"
                    color="positive"
                  />
                </q-item-section>
              </q-item>

              <q-item
                v-if="!loading && !filteredOptions.length && !inviteCandidate"
              >
                <q-item-section class="text-grey-7">
                  {{ $t("publication.manage.assign_team.empty") }}
                </q-item-section>
              </q-item>
            </q-list>
          </q-card-section>
        </div>

        <q-separator v-if="multiple" vertical />

        <div v-if="multiple" class="col-5 column dialog-col-right">
          <q-card-section class="q-pb-none">
            <div class="text-caption text-grey-7 q-mb-xs">
              {{
                $t("publication.manage.assign_team.selected_heading", {
                  n: selected.length
                })
              }}
            </div>
            <q-list v-if="selected.length" dense separator>
              <q-item
                v-for="(item, idx) in selected"
                :key="chipKey(item)"
                class="selected-item"
              >
                <q-item-section avatar>
                  <q-icon
                    :name="isInviteChip(item) ? 'mail' : 'person'"
                    :color="isInviteChip(item) ? 'accent' : 'primary'"
                  />
                </q-item-section>
                <q-item-section>
                  <q-item-label>{{ chipLabel(item) }}</q-item-label>
                  <q-item-label
                    v-if="!isInviteChip(item) && item.user.email"
                    caption
                  >
                    {{ item.user.email }}
                  </q-item-label>
                  <q-item-label v-else-if="isInviteChip(item)" caption>
                    {{ $t("publication.manage.assign_team.invite_hint") }}
                  </q-item-label>
                </q-item-section>
                <q-item-section side>
                  <q-btn
                    flat
                    round
                    dense
                    size="sm"
                    icon="close"
                    :aria-label="$t('guiElements.form.cancel')"
                    @click="removeAt(idx)"
                  />
                </q-item-section>
              </q-item>
            </q-list>
            <p v-else class="text-grey-7 q-my-sm">
              {{ $t("publication.manage.assign_team.selected_empty") }}
            </p>
          </q-card-section>

          <q-separator />

          <q-card-section class="q-py-sm">
            <div class="text-caption text-grey-7 q-mb-xs">
              {{ messageLabel }}
            </div>
            <div class="optional-message">
              <editor-content :editor="editor" />
            </div>
          </q-card-section>
        </div>
      </div>

      <!-- Single-mode message editor stays beneath the list since
           there's no right column to host it. -->
      <template v-if="!multiple">
        <q-separator />
        <q-card-section class="q-py-sm">
          <div class="text-caption text-grey-7 q-mb-xs">
            {{ messageLabel }}
          </div>
          <div class="optional-message">
            <editor-content :editor="editor" />
          </div>
        </q-card-section>
      </template>

      <q-separator />

      <q-card-actions align="right" class="q-pa-md">
        <q-btn
          flat
          :label="$t('guiElements.form.cancel')"
          @click="onDialogCancel"
        />
        <q-btn
          color="accent"
          :loading="saving"
          :disable="!selected.length"
          :label="submitLabel"
          @click="submit"
        />
      </q-card-actions>
    </q-card>
  </q-dialog>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

// Bundled reviewer-add mutation: connects existing user ids and
// invites new emails (with a single shared message) in one
// transaction. Replaces the previous "one connect + N invites"
// fan-out so partial failures can't leave a half-saved state.
graphql(`
  mutation AddReviewers(
    $submission_id: ID!
    $connect: [ID!]
    $invite_emails: [String!]
    $message: String
  ) {
    addReviewers(
      input: {
        submission_id: $submission_id
        connect: $connect
        invite_emails: $invite_emails
        message: $message
      }
    ) {
      id
      reviewers {
        ...relatedUserFields
      }
    }
  }
`)

graphql(`
  query GetPublicationReviewTeamForAssign(
    $id: ID!
    $roles: [SubmissionUserRoles!]!
    $search: String
    $orderBy: [QueryPublicationUsersOrderByOrderByClause!]
  ) {
    publication(id: $id) {
      id
      # Capped at 10: a top-N "currently engaged" surface, not an
      # exhaustive picker. The free-text search narrows the same
      # query when the assigner needs someone outside the top of the
      # list, and a typed email always allows inviting a new user.
      # last_assigned_at is server-scoped to the same roles filter,
      # so sorting by it returns "most recently assigned in this
      # role" without needing per-role recency fields.
      users(roles: $roles, first: 10, search: $search, orderBy: $orderBy) {
        data {
          id
          as_coordinator_active_count
          as_reviewer_active_count
          last_assigned_at
          user {
            id
            ...relatedUserFields
            ...avatarImage
          }
        }
      }
    }
  }
`)
</script>

<script setup lang="ts">
import { computed, ref } from "vue"
import { DateTime } from "luxon"
import { useQuery, useMutation } from "@vue/apollo-composable"
import { useDialogPluginComponent } from "quasar"
import { useI18n } from "vue-i18n"
import { useEditor, EditorContent } from "@tiptap/vue-3"
import StarterKit from "@tiptap/starter-kit"
import Placeholder from "@tiptap/extension-placeholder"
import { useFeedbackMessages } from "src/use/guiElements"
import { useTimeAgo } from "src/use/timeAgo"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import {
  AddReviewersDocument,
  GetPublicationReviewTeamForAssignDocument,
  type GetPublicationReviewTeamForAssignQuery,
  type QueryPublicationUsersOrderByOrderByClause,
  QueryPublicationUsersOrderByColumn,
  SortOrder,
  type SubmissionUserRoles
} from "src/graphql/generated/graphql"
import {
  UPDATE_SUBMISSION_REVIEW_COORDINATORS,
  INVITE_REVIEW_COORDINATOR
} from "src/graphql/mutations"

type Role = "reviewer" | "review_coordinator"

interface Props {
  submissionId: string
  publicationId: string
  role: Role
  multiple?: boolean
  excludeUserIds?: string[]
}

const props = withDefaults(defineProps<Props>(), {
  multiple: false,
  excludeUserIds: () => []
})

// eslint-disable-next-line vue/define-emits-declaration
defineEmits([...useDialogPluginComponent.emits])
const { dialogRef, onDialogHide, onDialogOK, onDialogCancel } =
  useDialogPluginComponent()

const { t: _t } = useI18n()
// vue-i18n's typed `t` only declares the (key, named) and (key, plural)
// overloads — the (key, plural, named) form we use here for plural
// strings ("{n} active") works at runtime but TS rejects it. Cast
// once and reuse, matching the helper pattern in src/use/i18nPrefix.ts.
const t = _t as (...args: unknown[]) => string
const { newStatusMessage } = useFeedbackMessages()

type PoolEntry = NonNullable<
  GetPublicationReviewTeamForAssignQuery["publication"]
>["users"]["data"][number]
type PoolUser = PoolEntry["user"]
type SelectedItem = PoolEntry | string

const searchInput = ref("")
const selected = ref<SelectedItem[]>([])

// One role per dialog: the RC dialog surfaces existing review
// coordinators only, the reviewer dialog surfaces existing reviewers
// only. This keeps `last_assigned_at` (server-scoped to the same
// `roles` filter) unambiguous — it represents "most recent
// assignment in *this* role" without needing role-specific recency
// fields.
const queryRoles = computed<SubmissionUserRoles[]>(() =>
  props.role === "review_coordinator"
    ? (["review_coordinator"] as SubmissionUserRoles[])
    : (["reviewer"] as SubmissionUserRoles[])
)

// Most recently assigned first. Same column for both dialogs because
// the underlying value is already role-scoped server-side.
const queryOrderBy = computed<QueryPublicationUsersOrderByOrderByClause[]>(
  () => [
    {
      column: QueryPublicationUsersOrderByColumn.LAST_ASSIGNED_AT,
      order: SortOrder.DESC
    }
  ]
)

const variables = computed(() => ({
  id: props.publicationId,
  roles: queryRoles.value,
  search: searchInput.value || undefined,
  orderBy: queryOrderBy.value
}))

const { result, loading } = useQuery(
  GetPublicationReviewTeamForAssignDocument,
  variables
)

const allOptions = computed<PoolEntry[]>(
  () => result.value?.publication?.users.data ?? []
)

const filteredOptions = computed<PoolEntry[]>(() =>
  allOptions.value.filter(
    (entry) => !props.excludeUserIds.includes(entry.user.id)
  )
)

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/

// Show an "Invite by email" pseudo-row only when the search text
// looks like an email and no pool option matches it.
const inviteCandidate = computed(() => {
  const v = searchInput.value.trim().toLowerCase()
  if (!v || !EMAIL_RE.test(v)) return null
  const matched = allOptions.value.some((e) => e.user.email.toLowerCase() === v)
  return matched ? null : v
})

function isInviteChip(item: SelectedItem): item is string {
  return typeof item === "string"
}

function isUserSelected(entry: PoolEntry): boolean {
  return selected.value.some(
    (s) => !isInviteChip(s) && s.user.id === entry.user.id
  )
}

function isInviteSelected(email: string): boolean {
  return selected.value.some(
    (s) => isInviteChip(s) && s.toLowerCase() === email.toLowerCase()
  )
}

function toggleUser(entry: PoolEntry) {
  if (props.multiple) {
    if (isUserSelected(entry)) {
      selected.value = selected.value.filter(
        (s) => isInviteChip(s) || s.user.id !== entry.user.id
      )
    } else {
      selected.value = [...selected.value, entry]
    }
  } else {
    selected.value = isUserSelected(entry) ? [] : [entry]
  }
}

function toggleInvite(email: string) {
  if (props.multiple) {
    if (isInviteSelected(email)) {
      selected.value = selected.value.filter(
        (s) => !(isInviteChip(s) && s.toLowerCase() === email.toLowerCase())
      )
    } else {
      selected.value = [...selected.value, email]
      // Clear so the user can keep adding more emails without manually
      // wiping the field.
      searchInput.value = ""
    }
  } else {
    selected.value = isInviteSelected(email) ? [] : [email]
    searchInput.value = ""
  }
}

function removeAt(idx: number) {
  selected.value = selected.value.filter((_, i) => i !== idx)
}

function chipKey(item: SelectedItem): string {
  return isInviteChip(item) ? `invite:${item}` : `user:${item.user.id}`
}

function chipLabel(item: SelectedItem): string {
  return isInviteChip(item) ? item : displayName(item.user)
}

function displayName(user: PoolUser): string {
  return user.name || user.username || user.email
}

// Each pool entry shows two lines of workload context:
//   primary  = the role we're assigning _for_ (so the admin sees
//              "{n} active reviewing" when picking reviewers, and
//              "{n} active coordinating" when picking RCs)
//   secondary = the same person's load in the OTHER role, so a
//              candidate isn't picked while already overloaded
//              elsewhere on the publication
//
// Strings are role-named so they read correctly regardless of which
// dialog is open: e.g. the RC dialog primary line says "coordinating"
// and the reviewer dialog primary says "reviewing".
function countLabelFor(
  slug: "coordinator" | "reviewer",
  n: number,
  prefix = ""
): string {
  return prefix + t(`publication.manage.assign_team.count_${slug}`, n, { n })
}

function activeCountLabel(entry: PoolEntry): string {
  if (props.role === "review_coordinator") {
    return countLabelFor("coordinator", entry.as_coordinator_active_count)
  }
  return countLabelFor("reviewer", entry.as_reviewer_active_count)
}

function otherRoleCount(entry: PoolEntry): number {
  return props.role === "review_coordinator"
    ? entry.as_reviewer_active_count
    : entry.as_coordinator_active_count
}

function otherRoleLabel(entry: PoolEntry): string {
  if (props.role === "review_coordinator") {
    return countLabelFor("reviewer", entry.as_reviewer_active_count, "+")
  }
  return countLabelFor("coordinator", entry.as_coordinator_active_count, "+")
}

const timeAgo = useTimeAgo()
function lastAssignedLabel(entry: PoolEntry): string {
  if (!entry.last_assigned_at) return ""
  const dt = DateTime.fromISO(entry.last_assigned_at as string)
  if (!dt.isValid) return ""
  return t("publication.manage.assign_team.last_assigned", {
    relative: timeAgo.format(dt.toJSDate(), "long")
  })
}

const submitLabel = computed(() => {
  const n = selected.value.length
  if (props.role === "review_coordinator") {
    return t("publication.manage.assign_team.assign_coordinator")
  }
  return t("publication.manage.assign_team.assign_reviewers", n, { n })
})

const dialogTitle = computed(() =>
  props.role === "review_coordinator"
    ? t("publication.manage.assign_team.title_coordinator")
    : t("publication.manage.assign_team.title_reviewers")
)

// RC dialog can only ever produce a single invitation, so its
// message helper text drops the "everyone" framing that suits the
// reviewer dialog's bulk-invite flow.
const messageLabel = computed(() =>
  props.multiple
    ? t("publication.manage.assign_team.message_label_many")
    : t("publication.manage.assign_team.message_label_one")
)

const editor = useEditor({
  editorProps: {
    attributes: {
      title: t("publication.manage.assign_team.message_placeholder")
    }
  },
  content: "",
  extensions: [
    StarterKit,
    Placeholder.configure({
      placeholder: t("publication.manage.assign_team.message_placeholder")
    })
  ]
})

// Reviewer flow uses the bundled addReviewers mutation (one round
// trip, transactional). RC flow stays on the legacy connect/invite
// pair because it's single-select — at most one user OR one email,
// no batching to gain.
const { mutate: addReviewers } = useMutation(AddReviewersDocument, {
  refetchQueries: ["GetManagedSubmission"]
})
const { mutate: connectCoordinator } = useMutation(
  UPDATE_SUBMISSION_REVIEW_COORDINATORS,
  { refetchQueries: ["GetManagedSubmission"] }
)
const { mutate: inviteCoordinator } = useMutation(INVITE_REVIEW_COORDINATOR, {
  refetchQueries: ["GetManagedSubmission"]
})

const saving = ref(false)

async function submit() {
  if (saving.value || !selected.value.length) return

  saving.value = true
  const userIds: string[] = []
  const inviteEmails: string[] = []
  for (const item of selected.value) {
    if (isInviteChip(item)) inviteEmails.push(item)
    else userIds.push(item.user.id)
  }

  const message = editor.value?.getText() ?? ""

  try {
    if (props.role === "reviewer") {
      await addReviewers({
        submission_id: props.submissionId,
        connect: userIds.length ? userIds : undefined,
        invite_emails: inviteEmails.length ? inviteEmails : undefined,
        message: message || undefined
      })
    } else {
      // RC: at most one user or one email. Connect first if present,
      // else invite the lone email.
      if (userIds.length) {
        await connectCoordinator({
          id: props.submissionId,
          connect: userIds
        })
      }
      for (const email of inviteEmails) {
        await inviteCoordinator({
          id: props.submissionId,
          email,
          message
        })
      }
    }
    newStatusMessage(
      "success",
      t("publication.manage.assign_team.success", selected.value.length, {
        n: selected.value.length
      })
    )
    onDialogOK()
  } catch {
    newStatusMessage("failure", t("publication.manage.assign_team.error"))
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.assign-team-dialog {
  /* Bound the dialog height so the scrollable list doesn't push the
     footer off-screen on shorter viewports — the search and actions
     stay anchored. */
  max-height: min(80vh, 720px);
}
/* Body row that hosts the two columns in multi mode. Min-height: 0
   plus overflow on the columns lets each side scroll independently
   without one pushing the other off-screen. */
.dialog-body {
  min-height: 0;
}
.dialog-col-right {
  /* Selected list grows; message stays anchored at the bottom of
     the column. */
  min-height: 0;
}
.scroll-area {
  overflow-y: auto;
  min-height: 0;
}
/* Folded-corner marker for staged users — siblings keep the icon
   crisp while the bg span carries the clip-path. */
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
  /* Info, not warning — page already uses warning for "needs your
     action" flags; the staged user state is informational, not a
     CTA for the admin. */
  background: var(--q-info);
  clip-path: polygon(100% 0, 0 0, 100% 100%);
  border-top-right-radius: 4px;
}
.staged-corner-icon {
  position: absolute;
  top: 1px;
  right: 1px;
  /* Quasar's `info` is a light-blue; white-on-light-blue is too
     low contrast. Dark-blue/near-black icon reads cleanly without
     pulling the eye away from the avatar. */
  color: rgba(0, 0, 0, 0.78);
}
.body--dark .staged-corner-bg {
  filter: brightness(1.05);
}
.selected-row {
  background: rgba(0, 128, 0, 0.06);
}
.body--dark .selected-row {
  background: rgba(0, 200, 100, 0.08);
}
.invite-row {
  background: rgba(33, 150, 243, 0.04);
}
.body--dark .invite-row {
  background: rgba(33, 150, 243, 0.08);
}
.optional-message :deep(.ProseMirror) {
  background-color: #fff;
  border: 1px solid #c2c2c2;
  border-radius: 5px;
  min-height: 4rem;
  padding: 8px;
}
.body--dark .optional-message :deep(.ProseMirror) {
  background-color: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.2);
  color: #fff;
}
.optional-message :deep(.ProseMirror p.is-editor-empty:first-child::before) {
  color: #666667;
  content: attr(data-placeholder);
  float: left;
  height: 0;
  pointer-events: none;
}
</style>

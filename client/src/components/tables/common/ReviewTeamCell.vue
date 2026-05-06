<template>
  <q-td :props="scope" :dense="scope.dense">
    <span class="sr-only">{{ ariaLabel }}</span>
    <template v-if="showExpanded">
      <div class="q-py-xs row no-wrap items-center">
        <component
          :is="coordinatorLink ? 'router-link' : 'div'"
          class="row items-center user-link-row col"
          :class="coordinatorLink ? 'is-link' : ''"
          :to="coordinatorLink || undefined"
          @click.stop
        >
          <div class="relative-position q-mr-sm">
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
          <div class="column" style="min-width: 0">
            <span class="ellipsis">
              {{ coordinator?.name ?? $t("review_team.no_coordinator") }}
            </span>
            <span
              v-if="coordinator?.username"
              class="text-caption text-grey-7 ellipsis"
            >
              {{ coordinator.username }}
            </span>
          </div>
        </component>
        <q-btn
          v-if="reviewers.length > 0"
          flat
          dense
          round
          size="xs"
          icon="unfold_less"
          aria-label="Collapse review team"
          :aria-expanded="true"
          @click.stop="expanded = false"
        >
          <q-tooltip>Collapse</q-tooltip>
        </q-btn>
      </div>
      <q-separator v-if="reviewers.length > 0" class="q-my-sm" />
      <component
        :is="teamMemberLinkTo(r.id) ? 'router-link' : 'div'"
        v-for="r in reviewers"
        :key="r.id"
        class="q-py-xs row items-center user-link-row"
        :class="teamMemberLinkTo(r.id) ? 'is-link' : ''"
        :to="teamMemberLinkTo(r.id) || undefined"
        @click.stop
      >
        <avatar-image :user="r" size="40px" rounded class="q-mr-sm" />
        <div class="column" style="min-width: 0">
          <span class="ellipsis">{{ r.name ?? r.email }}</span>
          <span v-if="r.username" class="text-caption text-grey-7 ellipsis">
            {{ r.username }}
          </span>
        </div>
      </component>
    </template>
    <template v-else>
      <q-item class="q-pa-none" role="group" :aria-label="ariaLabel">
        <q-item-section>
          <div class="row items-center no-wrap">
            <component
              :is="coordinatorLink ? 'router-link' : 'div'"
              class="relative-position user-avatar-link"
              :class="coordinatorLink ? 'is-link' : ''"
              :to="coordinatorLink || undefined"
              @click.stop
            >
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
              >
                RC
              </q-badge>
              <q-tooltip :delay="200" anchor="top middle" self="bottom middle">
                {{ coordinator?.name ?? $t("review_team.no_coordinator") }}
                ({{ $t("publication.dashboard.headers.review_coordinators") }})
              </q-tooltip>
            </component>
            <q-separator
              v-if="reviewers.length > 0"
              vertical
              class="q-mx-sm"
              color="grey-7"
              style="height: 48px"
            />
            <div v-if="reviewers.length > 0" class="row items-center no-wrap">
              <component
                :is="teamMemberLinkTo(r.id) ? 'router-link' : 'div'"
                v-for="(r, idx) in reviewers"
                :key="r.id"
                class="relative-position user-avatar-link"
                :class="[
                  idx > 0 ? 'q-ml-sm' : '',
                  teamMemberLinkTo(r.id) ? 'is-link' : ''
                ]"
                :to="teamMemberLinkTo(r.id) || undefined"
                @click.stop
              >
                <avatar-image :user="r" size="40px" rounded />
                <q-tooltip
                  :delay="200"
                  anchor="top middle"
                  self="bottom middle"
                >
                  {{ r.name ?? r.email }}
                </q-tooltip>
              </component>
            </div>
            <q-space v-if="reviewers.length > 0" />
            <q-btn
              v-if="reviewers.length > 0"
              flat
              dense
              round
              size="xs"
              icon="unfold_more"
              aria-label="Expand review team"
              :aria-expanded="false"
              class="q-ml-sm"
              @click.stop="expanded = true"
            >
              <q-tooltip>Expand</q-tooltip>
            </q-btn>
          </div>
        </q-item-section>
      </q-item>
    </template>
  </q-td>
</template>

<script lang="ts">
import type { InjectionKey, Ref } from "vue"

/**
 * Optional injection key for parent-driven expand-all behavior.
 */
export const ReviewTeamExpandAllKey: InjectionKey<Ref<boolean>> = Symbol(
  "ReviewTeamExpandAll"
)
</script>

<script setup lang="ts">
import { computed, inject, ref, watch } from "vue"
import { useI18n } from "vue-i18n"
import { useRoute } from "vue-router"
import type { RouteLocationRaw } from "vue-router"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import type { QTableBodyCellScope } from "../QueryTable.vue"

interface TeamUser {
  id: string
  name?: string | null
  email: string
  username?: string | null
}

interface TeamValue {
  coordinator: TeamUser | null
  reviewers: TeamUser[]
}

interface Props {
  scope: QTableBodyCellScope
}

const props = defineProps<Props>()
const { t } = useI18n()
const route = useRoute()

// The cell lives under /manage/publication/:id/... so the route
// param is the scoping publication id used to build team-member
// detail links.
const publicationId = computed(() => {
  const raw = (route.params as Record<string, string | string[] | undefined>).id
  return Array.isArray(raw) ? raw[0] : raw
})

function teamMemberLinkTo(
  userId: string | undefined | null
): RouteLocationRaw | null {
  if (!userId || !publicationId.value) return null
  return {
    name: "manage:publication:team_member" as const,
    params: { id: publicationId.value, userId }
  }
}

const team = computed(() => (props.scope.value as TeamValue) ?? null)
const coordinator = computed<TeamUser | null>(
  () => team.value?.coordinator ?? null
)
const reviewers = computed<TeamUser[]>(() => team.value?.reviewers ?? [])

const coordinatorLink = computed(() =>
  teamMemberLinkTo(coordinator.value?.id ?? null)
)

const expanded = ref(false)

// Only the reviewers list drives expand/collapse; a row with just a
// coordinator is always rendered fully.
const showExpanded = computed(
  () => reviewers.value.length === 0 || expanded.value
)

const ariaLabel = computed(() => {
  const coordName =
    coordinator.value?.name ??
    coordinator.value?.email ??
    t("review_team.no_coordinator")
  const reviewerNames = reviewers.value
    .map((r) => r.name ?? r.email)
    .filter(Boolean)
    .join(", ")
  if (!reviewerNames) {
    return `${t(
      "publication.dashboard.headers.review_coordinators"
    )}: ${coordName}`
  }
  return `${t(
    "publication.dashboard.headers.review_coordinators"
  )}: ${coordName}. ${t(
    "publication.dashboard.headers.reviewers"
  )}: ${reviewerNames}`
})

// Sync with a parent-provided expand-all state if present.
const expandAll = inject(ReviewTeamExpandAllKey, null)
if (expandAll) {
  watch(
    expandAll,
    (v) => {
      expanded.value = v
    },
    { immediate: true }
  )
}
</script>

<style scoped>
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
  top: -6px;
  right: -5px;
  z-index: 2;
}
.user-link-row.is-link,
.user-avatar-link.is-link {
  text-decoration: none;
  color: inherit;
  border-radius: 4px;
}
.user-link-row.is-link {
  padding: 2px 4px;
  margin: -2px -4px;
}
.user-link-row.is-link:hover {
  background: rgba(0, 0, 0, 0.04);
}
.body--dark .user-link-row.is-link:hover {
  background: rgba(255, 255, 255, 0.06);
}
.user-avatar-link.is-link:hover {
  box-shadow: 0 0 0 2px var(--q-primary);
}
</style>

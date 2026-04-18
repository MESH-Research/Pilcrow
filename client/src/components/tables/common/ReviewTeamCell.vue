<template>
  <q-td :props="scope" :dense="scope.dense">
    <template v-if="coordinator || reviewers.length">
      <span class="sr-only">{{ ariaLabel }}</span>
      <template v-if="showExpanded">
        <q-item class="q-px-none q-py-xs">
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
            <q-item-label v-if="coordinator" caption>
              <template v-if="coordinator.username">
                {{ coordinator.username }} &middot;
              </template>
              {{ $t("publication.dashboard.headers.review_coordinators") }}
            </q-item-label>
          </q-item-section>
          <q-item-section v-if="reviewers.length > 0" side>
            <q-btn
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
          </q-item-section>
        </q-item>
        <q-separator v-if="reviewers.length > 0" class="q-my-sm" />
        <q-item v-for="r in reviewers" :key="r.id" class="q-px-none q-py-xs">
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
      </template>
      <template v-else>
        <q-item class="q-pa-none" role="group" :aria-label="ariaLabel">
          <q-item-section>
            <div class="row items-center no-wrap">
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
                <q-tooltip
                  :delay="200"
                  anchor="top middle"
                  self="bottom middle"
                >
                  {{ coordinator?.name ?? $t("review_team.no_coordinator") }}
                  ({{
                    $t("publication.dashboard.headers.review_coordinators")
                  }})
                </q-tooltip>
              </div>
              <q-separator
                v-if="reviewers.length > 0"
                vertical
                class="q-mx-sm"
                color="grey-7"
                style="height: 48px"
              />
              <div v-if="reviewers.length > 0" class="row items-center no-wrap">
                <div
                  v-for="(r, idx) in reviewers"
                  :key="r.id"
                  class="relative-position"
                  :class="idx > 0 ? 'q-ml-sm' : ''"
                >
                  <avatar-image :user="r" size="40px" rounded />
                  <q-tooltip
                    :delay="200"
                    anchor="top middle"
                    self="bottom middle"
                  >
                    {{ r.name ?? r.email }}
                  </q-tooltip>
                </div>
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
    </template>
    <span v-else class="text-grey" aria-label="No review team assigned">
      &mdash;
    </span>
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

const team = computed(() => (props.scope.value as TeamValue) ?? null)
const coordinator = computed<TeamUser | null>(
  () => team.value?.coordinator ?? null
)
const reviewers = computed<TeamUser[]>(() => team.value?.reviewers ?? [])

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
</style>

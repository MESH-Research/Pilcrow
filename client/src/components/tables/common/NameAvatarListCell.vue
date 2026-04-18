<template>
  <q-td :props="scope" :dense="scope.dense">
    <template v-if="users.length">
      <span class="sr-only">{{ ariaLabel }}</span>
      <template v-if="showExpanded">
        <q-item
          v-for="(user, idx) in users"
          :key="user.email"
          class="q-pa-none"
        >
          <q-item-section side>
            <avatar-image
              :user="user"
              size="40px"
              rounded
              :aria-label="user.name ?? user.email"
            />
          </q-item-section>
          <q-item-section>
            <q-item-label v-if="user.name">{{ user.name }}</q-item-label>
          </q-item-section>
          <q-item-section v-if="idx === 0 && users.length > 1" side>
            <q-btn
              flat
              dense
              round
              size="xs"
              icon="unfold_less"
              aria-label="Collapse user list"
              :aria-expanded="true"
              @click.stop="expanded = false"
            >
              <q-tooltip>Collapse</q-tooltip>
            </q-btn>
          </q-item-section>
        </q-item>
      </template>
      <div
        v-else
        class="row items-center no-wrap q-gutter-xs"
        role="group"
        :aria-label="ariaLabel"
      >
        <div
          v-for="user in users"
          :key="user.email"
          class="q-pa-none relative-position"
        >
          <avatar-image
            :user="user"
            size="40px"
            rounded
            :aria-label="user.name ?? user.email"
          />
          <q-tooltip>{{ user.name ?? user.email }}</q-tooltip>
        </div>
        <q-space v-if="users.length > 1" />
        <q-btn
          v-if="users.length > 1"
          flat
          dense
          round
          size="xs"
          icon="unfold_more"
          aria-label="Expand user list"
          :aria-expanded="false"
          class="q-ml-xs"
          @click.stop="expanded = true"
        >
          <q-tooltip>Expand</q-tooltip>
        </q-btn>
      </div>
    </template>
    <span v-else class="text-grey" aria-label="No users assigned">&mdash;</span>
  </q-td>
</template>

<script lang="ts">
import type { InjectionKey, Ref } from "vue"

/**
 * Optional injection key. When a parent provides this ref, all
 * NameAvatarListCell instances below will sync their expanded state
 * with it, enabling a dashboard-level expand/collapse all control.
 */
export const NameAvatarListExpandAllKey: InjectionKey<Ref<boolean>> = Symbol(
  "NameAvatarListExpandAll"
)
</script>

<script setup lang="ts">
import { computed, inject, ref, watch } from "vue"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import { useI18n } from "vue-i18n"
import type { NameAvatarCellFragment } from "src/graphql/generated/graphql"
import type { QTableBodyCellScope } from "../QueryTable.vue"

interface Props {
  scope: QTableBodyCellScope
}

const props = defineProps<Props>()
const { t } = useI18n()

const users = computed(
  () => (props.scope.value as NameAvatarCellFragment[]) ?? []
)

const expanded = ref(false)

// Sync with a parent-provided expand-all state if present.
const expandAll = inject(NameAvatarListExpandAllKey, null)
if (expandAll) {
  watch(
    expandAll,
    (value) => {
      expanded.value = value
    },
    { immediate: true }
  )
}

// Single-user rows always render in the full avatar+name layout;
// multi-user rows are collapsed by default and expandable.
const showExpanded = computed(() => users.value.length === 1 || expanded.value)

const ariaLabel = computed(() =>
  t("tables.name_avatar_list.users_assigned", users.value.length, {
    named: {
      names: users.value
        .map((u) => u.name ?? u.email)
        .filter(Boolean)
        .join(", ")
    }
  })
)
</script>

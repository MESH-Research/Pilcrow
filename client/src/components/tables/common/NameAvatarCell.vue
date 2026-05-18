<template>
  <q-td :props="scope" :dense="scope.dense">
    <div v-if="user" class="row items-center no-wrap q-gutter-sm">
      <avatar-image :user="user" size="40px" rounded />
      <div class="column">
        <div v-if="user.name">{{ user.name }}</div>
        <div
          v-if="user.username && !hideUsername"
          class="text-caption text-grey-8"
        >
          {{ user.username }}
        </div>
      </div>
    </div>
    <span
      v-else
      class="text-grey-8"
      :aria-label="$t('admin.users.no_user_assigned')"
      >&mdash;</span
    >
  </q-td>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment NameAvatarCell on User {
    name
    username
    ...avatarImage
  }
`)
</script>

<script setup lang="ts">
import { computed } from "vue"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import type { NameAvatarCellFragment as NameAvatarCellType } from "src/graphql/generated/graphql"
import type { QTableBodyCellScope, QueryTableColumn } from "../QueryTable.vue"

interface Props {
  scope: QTableBodyCellScope
}

const props = defineProps<Props>()

const user = computed(
  () => (props.scope.value as NameAvatarCellType | null) ?? null
)

const hideUsername = computed(
  () => (props.scope.col as QueryTableColumn).hideUsername === true
)
</script>

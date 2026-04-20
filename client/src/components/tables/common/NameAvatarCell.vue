<template>
  <q-td :props="scope" :dense="scope.dense">
    <component
      :is="link ? 'router-link' : 'div'"
      v-if="user"
      :to="link || undefined"
      :class="['name-avatar-cell row items-center', link ? 'is-link' : '']"
      @click.stop
    >
      <avatar-image :user="user" size="40px" rounded class="q-mr-sm" />
      <span class="column" style="min-width: 0">
        <span class="ellipsis">{{ user.name ?? user.email }}</span>
        <span
          v-if="user.username && !hideUsername"
          class="text-caption text-grey-7 ellipsis"
        >
          {{ user.username }}
        </span>
      </span>
    </component>
    <span v-else class="text-grey" aria-label="No user assigned">&mdash;</span>
  </q-td>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment NameAvatarCell on User {
    name
    username
    email
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

// Optional: the column config can wire a `linkTo(row)` callback to
// make each cell click through to a detail page. Returning nullish
// skips the wrapping so rows without a destination stay static.
const link = computed(() => {
  const col = props.scope.col as QueryTableColumn
  return col.linkTo && user.value ? col.linkTo(props.scope.row) : null
})
</script>

<style scoped>
.name-avatar-cell {
  min-width: 0;
}
.name-avatar-cell.is-link {
  text-decoration: none;
  color: inherit;
  border-radius: 4px;
}
.name-avatar-cell.is-link:hover {
  text-decoration: underline;
}
</style>

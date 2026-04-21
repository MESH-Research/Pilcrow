<template>
  <q-td :props="scope" :dense="scope.dense">
    <q-item
      v-if="user"
      class="q-pa-none"
      :class="link ? 'user-item-link' : ''"
      :clickable="!!link"
      :to="link || undefined"
      @click.stop
    >
      <q-item-section side>
        <avatar-image :user="user" size="40px" rounded />
      </q-item-section>
      <q-item-section>
        <q-item-label>
          {{ user.name ?? user.email }}
        </q-item-label>
        <q-item-label v-if="user.username && !hideUsername" caption>
          {{ user.username }}
        </q-item-label>
      </q-item-section>
    </q-item>
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
/* Clickable q-item that navigates to a user detail page. Using
   Quasar's built-in `:to` + `clickable` keeps the q-item layout
   (avatar + stacked labels + proper vertical padding) intact.
   The default q-focus-helper tints with the primary color (blue)
   on hover/focus, which looks like an accidental highlight inside
   a table — suppress it and provide our own neutral hover fill. */
.user-item-link {
  border-radius: 4px;
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
:deep(.q-item__label + .q-item__label) {
  margin-top: 0;
}
:deep(.q-item__label) {
  line-height: 1.25;
}
</style>

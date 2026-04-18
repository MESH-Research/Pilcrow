<template>
  <q-td :props="scope" :dense="scope.dense">
    <q-item v-if="user" class="q-pa-none">
      <q-item-section side>
        <avatar-image :user="user" size="40px" rounded />
      </q-item-section>

      <q-item-section>
        <q-item-label v-if="user.name">
          {{ user.name }}
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

<style scoped>
:deep(.q-item__label + .q-item__label) {
  margin-top: 0;
}
:deep(.q-item__label) {
  line-height: 1.25;
}
</style>

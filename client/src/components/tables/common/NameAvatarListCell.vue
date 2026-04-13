<template>
  <q-td :props="scope" :dense="scope.dense">
    <div v-if="users.length" class="row no-wrap items-center q-gutter-xs">
      <div v-for="user in users" :key="user.email" class="q-pa-none">
        <avatar-image :user="user" size="sm" rounded>
          <q-tooltip>{{ user.name ?? user.email }}</q-tooltip>
        </avatar-image>
      </div>
    </div>
    <span v-else class="text-grey">&mdash;</span>
  </q-td>
</template>

<script setup lang="ts">
import { computed } from "vue"
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import type { NameAvatarCellFragment } from "src/graphql/generated/graphql"
import type { QTableBodyCellScope } from "../QueryTable.vue"

interface Props {
  scope: QTableBodyCellScope
}

const props = defineProps<Props>()

const users = computed(
  () => (props.scope.value as NameAvatarCellFragment[]) ?? []
)
</script>

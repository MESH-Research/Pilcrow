<template>
  <q-td :props="scope" :dense="scope.dense">
    <q-item class="q-pa-none">
      <q-item-section side>
        <avatar-image :user="scope.row" rounded />
      </q-item-section>

      <q-item-section>
        <q-item-label v-if="scope.row.name">
          {{ scope.row.name }}
        </q-item-label>
      </q-item-section>
    </q-item>
  </q-td>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment NameAvatarCell on User {
    name
    ...avatarImage
  }
`)
</script>

<script setup lang="ts">
import AvatarImage from "src/components/atoms/AvatarImage.vue"
import type { NameAvatarCellFragment as NameAvatarCellType } from "src/graphql/generated/graphql"

interface Props {
  scope: {
    col: Record<string, unknown>
    value: unknown
    row: NameAvatarCellType
    dense?: boolean
  }
}

defineProps<Props>()
</script>

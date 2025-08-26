<template>
  <q-list v-if="users.length" :data-cy="dataCy" role="list">
    <user-list-item
      v-for="user in users"
      :key="user.id"
      :user="user"
      :t-prefix="tPrefix"
      :mutable="mutable"
      @unassign="(args) => $emit('unassign', args)"
      @reinvite="(args) => $emit('reinvite', args)"
    />
  </q-list>
  <q-card v-else class="text--grey" bordered flat>
    <q-card-section horizontal>
      <q-card-section>
        <q-icon color="accent" name="o_do_disturb_on" size="sm" />
      </q-card-section>
      <q-card-section>
        <slot name="no-data">
          {{ $t(tKey("no-data")) }}
        </slot>
      </q-card-section>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import type { UserListFragment } from "src/gql/graphql"
import UserListItem from "../atoms/UserListItem.vue"

interface Props {
  users: UserListFragment[]
  mutable: boolean
  dataCy?: string
  tPrefix?: string
}

const { dataCy = "user_list", users, tPrefix = "" } = defineProps<Props>()

interface Emits {
  (e: "unassign", user: UserListFragment): void
  (e: "reinvite", user: UserListFragment): void
}
defineEmits<Emits>()

const tKey = (key: string) => (tPrefix.length ? `${tPrefix}.${key}` : key)
</script>

<script lang="ts">
graphql(`
  fragment UserList on User {
    ...UserListItem
  }
`)
</script>

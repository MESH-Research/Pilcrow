<template>
  <section class="column q-gutter-y-sm">
    <h3 class="q-my-none">{{ tp$("heading") }}</h3>
    <p v-if="te(tKey('description'))" class="q-mb-none q-mx-none">
      {{ tp$("description") }}
    </p>
    <div v-if="users.length">
      <UserList
        ref="userList"
        :mutable="mutable"
        :t-prefix="tPrefix"
        data-cy="user-list"
        :users="users"
        @unassign="(args) => $emit('unassign', args)"
        @reinvite="(args) => $emit('reinvite', args)"
      />
    </div>
    <FindUserSelect
      v-if="acceptMore"
      ref="findUserSelectRef"
      data-cy="input_user"
      :can-invite="canInvite"
      @add="(user) => $emit('add', user)"
      @invite="(user) => $emit('invite', user)"
    />
  </section>
</template>

<script setup lang="ts">
import type {
  NewStagedUser,
  SearchUsersSelected
} from "./forms/FindUserSelect.vue"
import UserList from "./molecules/UserList.vue"
import FindUserSelect from "./forms/FindUserSelect.vue"

import type { AssignedUsersFragment } from "src/gql/graphql"

interface Props {
  users: AssignedUsersFragment[]
  mutable?: boolean
  canInvite?: boolean
  maxUsers?: number | false
  tPrefix?: string
}

const props = withDefaults(defineProps<Props>(), {
  mutable: false,
  maxUsers: false,
  tPrefix: "",
  canInvite: false
})

interface Emits {
  (e: "unassign", user: AssignedUsersFragment): void
  (e: "reinvite", user: AssignedUsersFragment): void
  (e: "add", user: SearchUsersSelected): void
  (e: "invite", user: NewStagedUser): void
}

defineEmits<Emits>()
type FindUserSelectRef = InstanceType<typeof FindUserSelect>
const findUserSelectRef = useTemplateRef<FindUserSelectRef>("findUserSelectRef")

const { t, te } = useI18n()
const tKey = (key) => (props.tPrefix ? `${props.tPrefix}.${key}` : key)
const tp$ = (key, ...args) => t(tKey(key), args)

const acceptMore = computed(() => {
  const maxUsers = props.maxUsers === false ? Infinity : props.maxUsers
  return props.mutable && props.users.length < maxUsers
})

defineExpose({
  reset: () => {
    findUserSelectRef.value?.reset()
  }
})
</script>

<script lang="ts">
import { graphql } from "src/gql"

graphql(`
  fragment AssignedUsers on User {
    id
    ...UserList
  }
`)
</script>

<style></style>

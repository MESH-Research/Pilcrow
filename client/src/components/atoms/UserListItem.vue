<template>
  <q-item class="q-px-none" role="listitem">
    <q-item-section top avatar>
      <avatar-image :user="user" rounded />
    </q-item-section>
    <q-item-section>
      <q-item-label v-if="user.name">
        {{ user.name }}
      </q-item-label>
      <q-item-label v-else>
        {{ user.username }}
      </q-item-label>
      <q-item-label caption lines="1" class="text--grey">
        {{ user.email }}
      </q-item-label>
    </q-item-section>
    <q-item-section v-if="actions.length" side>
      <div class="q-gutter-xs">
        <q-btn
          v-if="user.staged"
          flat
          :label="$t(`submissions.reinvite.label`)"
          icon="schedule"
          data-cy="user_unconfirmed"
          @click="$emit('reinvite', { user })"
        >
          <q-tooltip anchor="top middle" self="center middle">{{
            $t("submissions.reinvite.tooltip")
          }}</q-tooltip>
        </q-btn>
        <q-btn
          v-for="{ ariaLabel, icon, action, help, cyAttr } in actions"
          :key="icon"
          size="12px"
          flat
          dense
          round
          :title="help"
          :icon="icon"
          :data-cy="`${cyAttr}`"
          :aria-label="`${ariaLabel} ${user.name || user.username}`"
          @click="$emit('actionClick', { user, action })"
        />
      </div>
    </q-item-section>
  </q-item>
</template>

<script setup lang="ts">
import type { UserListItemFragment } from "src/gql/graphql"
import AvatarImage from "./AvatarImage.vue"
export interface Action {
  ariaLabel: string
  icon: string
  action: string
  help: string
  cyAttr: string
}
interface Props {
  user: UserListItemFragment
  actions?: Action[]
}

const { user, actions = [] } = defineProps<Props>()
defineEmits(["actionClick", "reinvite"])
</script>

<script lang="ts">
graphql(`
  fragment UserListItem on User {
    id
    name
    email
    username
    staged
  }
`)
</script>

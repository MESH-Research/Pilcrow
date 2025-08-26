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
    <q-item-section v-if="mutable" side>
      <div class="q-gutter-xs">
        <q-btn
          v-if="user.staged"
          flat
          :label="$t(`submissions.reinvite.label`)"
          icon="schedule"
          data-cy="user_unconfirmed"
          @click="$emit('reinvite', user)"
        >
          <q-tooltip anchor="top middle" self="center middle">{{
            $t("submissions.reinvite.tooltip")
          }}</q-tooltip>
        </q-btn>
        <q-btn
          v-if="mutable"
          size="12px"
          flat
          dense
          round
          :title="t('unassign_button.title')"
          icon="person_remove"
          data-cy="button_unassign"
          aria-label="unassign_button.ariaLabel"
          @click="$emit('unassign', user)"
        />
      </div>
    </q-item-section>
  </q-item>
</template>

<script setup lang="ts">
import type { UserListItemFragment } from "src/gql/graphql"
import AvatarImage from "./AvatarImage.vue"

interface Props {
  user: UserListItemFragment
  tPrefix?: string
  mutable: boolean
}

const { user, mutable, tPrefix = "" } = defineProps<Props>()

interface Emits {
  (e: "unassign", user: UserListItemFragment): void
  (e: "reinvite", user: UserListItemFragment): void
}
defineEmits<Emits>()

const i18n = useI18n()
const tKey = (key) => (tPrefix.length ? `${tPrefix}.${key}` : key)
const t = (key, args?) => i18n.t(tKey(key), args)
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

<template>
  <q-item
    clickable
    class="q-px-lg"
    @click="$emit('actionClick', { user, action })"
  >
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
      <q-item-label caption>
        {{ user.email }}
      </q-item-label>
    </q-item-section>
  </q-item>
</template>

<script setup lang="ts">
import AvatarImage from "./AvatarImage.vue"
import type { avatarImageFragment } from "src/graphql/generated/graphql"

interface User extends avatarImageFragment {
  name?: string | null
  username?: string | null
}

interface Props {
  index?: number | null
  user?: User
  action?: string
}
withDefaults(defineProps<Props>(), {
  index: null,
  user: undefined,
  action: ""
})
interface Emits {
  actionClick: [payload: { user: User | undefined; action: string }]
}

defineEmits<Emits>()
</script>

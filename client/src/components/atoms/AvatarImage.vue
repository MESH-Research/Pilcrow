<template>
  <q-avatar v-bind="{ ...$attrs, ...$props }">
    <q-img :src="avatarSrc" alt="User Avatar" />
  </q-avatar>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment avatarImage on User {
    avatar_color
  }
`)
</script>

<script setup lang="ts">
import { computed } from "vue"
import type { avatarImageFragment } from "src/graphql/generated/graphql"

interface Props {
  user: avatarImageFragment
}

const props = defineProps<Props>()

const avatarSrc = computed(() => {
  if (!props.user.avatar_color) {
    return ""
  }
  return `/avatar/avatar-${props.user.avatar_color}.png`
})
</script>

<style lang="css">
.q-avatar::before {
  border-radius: 50%;
  bottom: 0;
  box-shadow:
    0 0 0 0.05rem #777,
    0 0 0 0.1rem #fff;
  content: "";
  display: block;
  left: 0;
  position: absolute;
  right: 0;
  top: 0;
  z-index: 1;
}
.q-avatar.rounded-borders::before {
  border-radius: 4px;
}
.q-avatar.q-avatar--square::before {
  border-radius: unset;
}
</style>

<template>
  <q-avatar v-bind="{ ...$attrs, ...$props }">
    <q-img :src="avatarSrc" alt="User Avatar" />
  </q-avatar>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment avatarImage on User {
    email
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

const stringToInt = (s: string): number => {
  let hash = 0
  if (s.length === 0) return hash
  for (let i = 0; i < s.length; i++) {
    const chr = s.charCodeAt(i)
    hash = (hash << 5) - hash + chr
    hash |= 0 // Convert to 32bit integer
  }
  return hash
}

const colors = [
  "blue",
  "cyan",
  "green",
  "magenta",
  "orange",
  "pine",
  "purple",
  "red",
  "yellow"
]

const avatarSrc = computed(() => {
  if (!props.user.email) {
    return ""
  }
  const number = Math.abs(stringToInt(props.user.email)) % colors.length
  return `/avatar/avatar-${colors[number]}.png`
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

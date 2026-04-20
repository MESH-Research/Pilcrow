<template>
  <q-avatar v-bind="{ ...$attrs, ...$props }">
    <q-img v-if="uploadedSrc" :src="uploadedSrc" alt="User Avatar" />
    <!-- eslint-disable-next-line vue/no-v-html -->
    <div
      v-else
      class="identicon-wrap"
      role="img"
      aria-label="User Avatar"
      v-html="identiconSvg"
    />
  </q-avatar>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment avatarImage on User {
    id
    email
    avatar {
      url
      thumb_url
      medium_url
    }
  }
`)
</script>

<script setup lang="ts">
import { computed } from "vue"
import { toSvg } from "jdenticon"
import type { avatarImageFragment } from "src/graphql/generated/graphql"

interface Props {
  user: avatarImageFragment
  /**
   * Which uploaded-avatar variant to display. Ignored if the user has no avatar.
   * Named `variant` (not `size`) so it doesn't collide with q-avatar's `size`
   * prop, which is still accepted via `$attrs` passthrough.
   */
  variant?: "thumb" | "medium" | "original"
}

const props = withDefaults(defineProps<Props>(), {
  variant: "thumb"
})

/**
 * The URL of an uploaded avatar, or an empty string when the user has not
 * uploaded one. When empty, the template falls back to a jdenticon SVG.
 */
const uploadedSrc = computed(() => {
  const avatar = props.user.avatar
  if (!avatar) return ""
  if (props.variant === "original" && avatar.url) return avatar.url
  if (props.variant === "medium" && avatar.medium_url) return avatar.medium_url
  if (avatar.thumb_url) return avatar.thumb_url
  if (avatar.url) return avatar.url
  return ""
})

/**
 * Seed the identicon from a stable per-user value, preferring the user id
 * (immutable) and falling back to the email. We intentionally do not seed
 * from mutable fields so users' default identicons stay consistent.
 */
const identiconSeed = computed(() => {
  if (props.user.id) return String(props.user.id)
  return props.user.email ?? ""
})

const identiconConfig = {
  padding: 0.08,
  lightness: {
    color: [0.4, 0.64] as [number, number],
    grayscale: [0.3, 0.63] as [number, number]
  },
  saturation: { color: 0.72, grayscale: 0.15 },
  backColor: "#0000"
}

const identiconSvg = computed(() => {
  if (!identiconSeed.value) return ""
  return toSvg(identiconSeed.value, 200, identiconConfig)
})
</script>

<style lang="css">
.identicon-wrap {
  width: 100%;
  height: 100%;
  display: flex;
}
.identicon-wrap svg {
  width: 100%;
  height: 100%;
}
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

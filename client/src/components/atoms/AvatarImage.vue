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
    <span
      v-if="showStagedCorner"
      class="avatar-staged-corner"
      role="img"
      :aria-label="$t('user.staged_corner.aria')"
    >
      <span class="avatar-staged-corner__bg" aria-hidden="true" />
      <q-icon name="schedule" class="avatar-staged-corner__icon" />
      <q-tooltip anchor="top middle" self="bottom middle">
        {{ $t("user.staged_corner.tooltip") }}
      </q-tooltip>
    </span>
  </q-avatar>
</template>

<script lang="ts">
import { graphql } from "src/graphql/generated"

graphql(`
  fragment avatarImage on User {
    id
    email
    staged
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
  /**
   * Suppress the staged-user corner marker even when `user.staged` is true.
   * Useful in contexts where the staged status is already conveyed elsewhere
   * (e.g. a sibling badge) and the corner would be redundant noise.
   */
  hideStaged?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: "thumb",
  hideStaged: false
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

const showStagedCorner = computed(
  () => props.user.staged === true && !props.hideStaged
)
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
/* Folded-corner marker for staged (invited-but-not-signed-in) users.
   The bg span carries the triangular clip-path, while the unclipped
   icon sits on top so it stays crisp inside the visible fold. The
   wrap sits inside q-avatar and is clipped to the avatar's outline,
   so the same markup looks correct on both circular and rounded
   avatars. */
.avatar-staged-corner {
  position: absolute;
  top: 0;
  right: 0;
  /* Proportions cribbed from the publication-admin assignee list,
     where a 24px corner on a 40px avatar (60%) read correctly. */
  width: 60%;
  height: 60%;
  /* Establish a query container so the icon and its offsets size
     to the visible triangle. Needed because q-icon's default
     font-size inherits from q-avatar, which is the full avatar
     size — fine inside table-sized avatars but causes the icon to
     overflow the triangle on profile-card-sized avatars. */
  container-type: inline-size;
  pointer-events: auto;
  z-index: 2;
}
.avatar-staged-corner__bg {
  position: absolute;
  inset: 0;
  /* Info (blue) — staged is informational state, not a CTA flag. */
  background: var(--q-info);
  clip-path: polygon(100% 0, 0 0, 100% 100%);
}
.avatar-staged-corner__icon {
  position: absolute;
  top: 4cqw;
  right: 4cqw;
  /* ~50% of the triangle width — matches publication-admin's
     12px-on-24px ratio, so the icon stays inside the visible fold
     at every avatar size without manual per-size tuning. */
  font-size: 50cqw;
  /* Quasar's `info` is light-blue; near-black reads cleanly. */
  color: rgba(0, 0, 0, 0.78);
}
.body--dark .avatar-staged-corner__bg {
  filter: brightness(1.05);
}
</style>

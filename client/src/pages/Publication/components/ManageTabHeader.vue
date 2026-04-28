<template>
  <span
    class="manage-tab-header row items-center no-wrap q-gutter-x-xs"
    :aria-label="actionNeeded && reason ? reason : undefined"
  >
    <q-icon
      v-if="actionNeeded"
      name="flag"
      size="xs"
      class="pattern-text-mask"
    />
    <span class="pattern-text-mask">{{ label }}</span>
    <!-- Tooltip on the wrapper so hover anywhere over the tab's
         content area surfaces the reason — not just hover on the
         flag icon. Skipped when there's no reason or the tab isn't
         in alert mode so non-alert tabs don't sprout empty popups. -->
    <q-tooltip
      v-if="actionNeeded && reason"
      anchor="bottom middle"
      self="top middle"
    >
      {{ reason }}
    </q-tooltip>
  </span>
</template>

<script lang="ts">
/**
 * Quasar utility classes that, applied to a q-tab, give it the
 * "needs your action" treatment used elsewhere in the manage UI:
 * warning fill, dark foreground, diagonal pattern overlay (gated
 * on `.a11y-patterns` / prefers-contrast: more).
 *
 * Exported so consumers can bind it on the q-tab itself when their
 * own action-needed flag flips, keeping the visual identity in one
 * place even though the colored area lives on the tab — not the
 * inner content.
 */
export const ACTION_NEEDED_TAB_CLASS = "bg-warning text-dark pattern-diagonal"
</script>

<script setup lang="ts">
// Inner content for a q-tab: a horizontally-laid-out flag icon
// (when action is needed) followed by the label. q-tab's default
// content layout is column-stacked so this wrapper enforces a row.
// Bg/color treatment lives on the q-tab itself — see
// ACTION_NEEDED_TAB_CLASS — so the colored area fills the full tab.

interface Props {
  label: string
  actionNeeded?: boolean
  // Optional explanation surfaced on the flag icon when actionNeeded
  // is true. Lets the caller spell out *why* the tab needs attention
  // (e.g. which list is missing) so the alert isn't just "something
  // is wrong here".
  reason?: string
}

withDefaults(defineProps<Props>(), {
  actionNeeded: false,
  reason: ""
})
</script>

<template>
  <header class="row items-center no-wrap q-gutter-x-sm section-header">
    <component
      :is="level"
      class="section-heading row items-center q-gutter-x-xs col q-my-none"
    >
      <span>{{ title }}</span>
      <span v-if="count !== null" class="text-grey-7 text-body2">
        ({{ count }})
      </span>
      <q-chip
        v-if="missing"
        dense
        color="warning"
        text-color="dark"
        icon="flag"
        class="q-ml-xs pattern-diagonal"
        :label="missingLabel ?? $t('submission.assignee_list.missing_badge')"
      />
    </component>
    <slot name="action" />
  </header>
</template>

<script setup lang="ts">
// Reusable header row for cards / panels in the manage UI.
// Composes a heading (h2 or h3) with an optional inline count,
// an optional "missing" warning chip, and an `action` slot for a
// right-aligned button. Visual style matches `.section-heading`
// (defined in app.sass) so headings stay consistent across pages.

interface Props {
  title: string
  level?: "h2" | "h3"
  // `null` (default) means "don't render a count". `0` is a real
  // value and renders as "(0)".
  count?: number | null
  missing?: boolean
  missingLabel?: string
}

withDefaults(defineProps<Props>(), {
  level: "h3",
  count: null,
  missing: false,
  missingLabel: undefined
})
</script>

<style scoped>
/* Slight size bump for h2 over the shared `.section-heading` so a
   panel title reads as the parent of any sub-section h3 inside it.
   `:deep` not needed — this scoped rule applies to the rendered
   element directly. */
.section-heading {
  font-size: 1.25rem;
}
h2.section-heading {
  font-size: 1.5rem;
  font-weight: 700;
  line-height: 1.3;
}
</style>

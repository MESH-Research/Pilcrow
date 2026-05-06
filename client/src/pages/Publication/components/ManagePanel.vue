<template>
  <q-card flat bordered class="q-mb-md manage-panel">
    <q-card-section v-if="title" class="q-py-sm">
      <section-header
        :title="title"
        :level="level"
        :count="count"
        :missing="missing"
        :missing-label="missingLabel"
      >
        <template v-if="$slots.action" #action>
          <slot name="action" />
        </template>
      </section-header>
    </q-card-section>
    <q-separator v-if="title" />
    <slot />
  </q-card>
</template>

<script setup lang="ts">
import SectionHeader from "src/components/atoms/SectionHeader.vue"

// Shared "section card" wrapper for the manage UI: a bordered card
// with a SectionHeader at the top, a separator, and a body slot.
// Use this for any panel that needs a labelled section — the three
// tabs on the submission detail page, the user-detail page stat
// strips, etc. — so headings, dividers, and card chrome stay
// consistent everywhere.

interface Props {
  // Optional: when omitted the panel renders a plain bordered card
  // (useful for surfaces that have no title row, like the user
  // header card on the submitter detail page).
  title?: string
  level?: "h2" | "h3"
  count?: number | null
  missing?: boolean
  missingLabel?: string
}

withDefaults(defineProps<Props>(), {
  title: "",
  level: "h3",
  count: null,
  missing: false,
  missingLabel: undefined
})
</script>

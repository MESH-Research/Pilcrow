<template>
  <article class="q-px-md">
    <h2>{{ $t("publications.style_criteria.heading") }}</h2>
    <p>{{ $t("publications.style_criteria.body_1") }}</p>
    <p>{{ $t("publications.style_criteria.body_2") }}</p>
    <q-banner
      v-if="publication.style_criterias.length === 0"
      inline-actions
      rounded
      class="highlight text-black"
    >
      <template #avatar>
        <q-icon name="tips_and_updates" />
      </template>
      {{ $t("publications.style_criteria.create") }}
    </q-banner>
    <publication-style-criteria :publication="publication" />
  </article>
</template>

<script setup lang="ts">
import PublicationStyleCriteria from "src/components/PublicationStyleCriteria.vue"

definePage({
  name: "publication:setup:criteria"
})

defineProps({
  publication: {
    type: Object,
    required: true
  }
})
</script>

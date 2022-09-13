<template>
  <article class="q-px-md">
    <h2>Style Criteria</h2>
    <p>
      Style criteria are a core component of the Collaborative Review Process.
      Your users will associate their comments with one or more of the style
      criteria you define below. This helps keep the conversation about a
      submission aligned with your publication's core values and focused on
      moving a submission through the review process.
    </p>
    <p>
      You can define up to six style critera and your reviewers are able to
      select mulitple criteria when creating a comment. Reviewers are able to
      bypass the style criteria selection after confirming that their comment
      doesn't fit any of the options presented.
    </p>
    <q-banner
      v-if="publication.style_criterias.length === 0"
      inline-actions
      rounded
      class="highlight text-black"
    >
      <template #avatar>
        <q-icon name="tips_and_updates" />
      </template>
      Create style criteria to help your reviewers.
    </q-banner>
    <publication-style-criteria :publication="publication" />
  </article>
</template>

<script setup>
import PublicationStyleCriteria from "src/components/PublicationStyleCriteria.vue"
defineProps({
  publication: {
    type: Object,
    required: true,
  },
})
</script>

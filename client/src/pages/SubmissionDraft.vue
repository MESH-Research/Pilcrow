<template>
  <nav class="q-px-lg q-pt-md q-gutter-sm">
    <q-breadcrumbs>
      <q-breadcrumbs-el label="My Submissions" />
      <q-breadcrumbs-el :label="submission?.publication?.name ?? ''" />
      <q-breadcrumbs-el> {{ submission?.title ?? "" }} Draft </q-breadcrumbs-el>
    </q-breadcrumbs>
  </nav>
  <article v-if="submission" class="q-pa-lg">
    <h2>Todo</h2>
    <div class="q-gutter-md">
      <q-banner class="bg-primary text-white" inline-actions="">
        <div>Update submssion details</div>
        <div class="text-caption">
          Update the title of your submission as well as enter your metadata,
          etc, etc
        </div>
        <template #action>
          <q-btn flat>Go</q-btn>
        </template>
      </q-banner>
      <q-banner class="bg-primary text-white" inline-actions>
        <div>Upload Submission Content</div>
        <div class="text-caption">Upload or paste your submission content.</div>
        <template #action>
          <q-btn
            flat
            :to="{
              name: 'submission:content',
              params: { id: submission.id },
            }"
          >
            Go
          </q-btn>
        </template>
      </q-banner>
      <q-banner class="bg-primary text-white" inline-actions>
        <div>Invite Collaborators</div>
        <div class="text-caption">
          Invite collaborators to join the review process.
        </div>
        <template #action>
          <q-btn flat>Skip</q-btn>
          <q-btn flat> Go </q-btn>
        </template>
      </q-banner>
    </div>
    <div>
      <h2>Submit for Review</h2>
      <div>Everything ready to go?</div>
      <q-btn color="primary">Submit for Review</q-btn>
    </div>
  </article>
</template>

<script setup>
import { GET_SUBMISSION } from "src/graphql/queries"
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})
const { result } = useQuery(GET_SUBMISSION, props)
const submission = computed(() => result.value?.submission)
</script>

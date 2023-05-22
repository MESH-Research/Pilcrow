<template>
  <nav class="q-px-lg q-pt-md q-gutter-sm">
    <q-breadcrumbs>
      <q-breadcrumbs-el label="Publications" />
      <q-breadcrumbs-el :label="submission?.publication?.name ?? 'Publication'" />
      <q-breadcrumbs-el> {{ submission?.title ?? "" }} Draft </q-breadcrumbs-el>
    </q-breadcrumbs>
  </nav>
  <article v-if="submission" class="q-pa-lg">
    <h2>Todo</h2>
    <div class="q-gutter-md">
      <!-- TODO: Develop metadata updating -->
      <!-- <submission-draft-todo-item title="Update submission details">
        Update the title of your submission as well as enter your metadata, etc,
        etc
      </submission-draft-todo-item> -->
      <submission-draft-todo-item
        title="Upload submission content"
        @go-click="onGoToSubmissionContentClick"
      >
        Upload or paste your submission content.
      </submission-draft-todo-item>
      <!-- TODO: Develop collaborator inviting -->
      <!-- <q-banner class="bg-grey-3" inline-actions>
        <div>Invite Collaborators</div>
        <div class="text-caption">
          Invite collaborators to join the review process.
        </div>
        <template #action>
          <q-btn flat>Skip</q-btn>
          <q-btn flat> Go </q-btn>
        </template>
      </q-banner> -->
    </div>
    <div>
      <h2>Submit for Review</h2>
      <div>Everything ready to go?</div>
      <q-btn class="q-mt-lg" color="primary">Submit for Review</q-btn>
    </div>
  </article>
</template>

<script setup>
import { useQuery } from "@vue/apollo-composable"
import SubmissionDraftTodoItem from "src/components/SubmissionDraftTodoItem.vue"
import { GET_SUBMISSION } from "src/graphql/queries"
import { computed } from "vue"
import { useRouter } from "vue-router"

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})
const { result } = useQuery(GET_SUBMISSION, props)
const submission = computed(() => result.value?.submission)

const { push } = useRouter()
function onGoToSubmissionContentClick() {
  push({
    name: "submission:content",
    params: { id: submission.value.id },
  })
}
</script>

<template>
  <div>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="$t('header.publications')"
          :to="{ name: 'publication:index' }"
        />
        <q-breadcrumbs-el
          v-if="publication"
          :label="publication?.name ?? ''"
          :to="{
            name: 'publication:home',
            params: { id: publication.id },
          }"
        />
        <q-breadcrumbs-el label="Submit a Work" />
      </q-breadcrumbs>
    </nav>
    <div class="q-pa-lg">
      <article v-if="!publication">Loading...</article>
      <article
        v-else-if="publication.is_accepting_submissions"
        class="q-gutter-md"
      >
        <!--  eslint-disable vue/no-v-html -->
        <div
          data-cy="publication_home_content"
          class="content"
          v-html="publication.new_submission_content"
        />
        <!--  eslint-enable vue/no-v-html -->
        <q-form @submit="createSubmission()">
          <div class="q-gutter-md column q-pl-none q-pr-md">
            <q-input
              v-model="submission_title"
              outlined
              label="Enter Submission Title"
              data-cy="new_submission_title_input"
            />
            <q-checkbox
              v-model="submission_agree"
              label="I have read and understand the submission guidelines and review process for this publication."
            />
          </div>
          <q-btn
            class="accent text-white q-mt-lg"
            type="submit"
            :disable="saving"
            :loading="saving"
            data-cy="save_submission"
          >
            Next
          </q-btn>
        </q-form>
      </article>
      <article v-else class="q-px-lg">
        <q-banner class="bg-primary">
          This publication is currently not accepting submissions.
          <template #action>
            <q-btn
              flat
              color="white"
              label="Return to Publication Home Page"
              :to="{
                name: 'publication:home',
                params: { id: publication.id },
              }"
            />
          </template>
        </q-banner>
      </article>
    </div>
  </div>
</template>

<script setup>
import { useQuery, useMutation } from "@vue/apollo-composable"
import { computed, ref } from "vue"
import { GET_PUBLICATION } from "src/graphql/queries"
import { CREATE_SUBMISSION_DRAFT } from "src/graphql/mutations"
import { useRouter } from "vue-router"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const { result } = useQuery(GET_PUBLICATION, props)

const publication = computed(() => result.value?.publication)

const submission_title = ref("")
const submission_agree = ref(false)

const { mutate, saving } = useMutation(CREATE_SUBMISSION_DRAFT)
const { push } = useRouter()

async function createSubmission() {
  const mutationResult = await mutate({
    title: submission_title.value,
    publication_id: props.id,
  })
  const submissionId = mutationResult?.data?.createSubmissionDraft?.id
  if (submissionId !== null) {
    push({ name: "submission:draft", params: { id: submissionId } })
  }
}
</script>

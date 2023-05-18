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
        <q-form @submit="handleSubmit()">
          <q-input
            v-model="v$.title.$model"
            :error="v$.title.$error"
            outlined
            label="Enter Submission Title"
            data-cy="new_submission_title_input"
          >
            <template #error>
              <error-field-renderer
                :errors="v$.title.$errors"
                prefix="submissions.create.title"
              />
            </template>
          </q-input>
          <!--  eslint-disable vue/no-v-html -->
          <div
            data-cy="publication_home_content"
            class="q-mt-md"
            v-html="publication.new_submission_content"
          />
          <!--  eslint-enable vue/no-v-html -->
          <q-field
            v-model="v$.acknowledgement.$model"
            borderless
            :error="v$.acknowledgement.$error"
            style="padding-right: 12px"
          >
            <q-checkbox
              v-model="v$.acknowledgement.$model"
              label="I have read and understand the submission guidelines and review process for this publication."
            />
            <template #error
              ><div class="q-pt-none">
                {{ $t(`submissions.create.acknowledgement.required`) }}
              </div></template
            >
          </q-field>
          <q-btn
            class="accent text-white q-mt-xl"
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
import { useQuery } from "@vue/apollo-composable"
import { computed } from "vue"
import { GET_PUBLICATION } from "src/graphql/queries"
import { useSubmissionCreation } from "src/use/submission"
import { useRouter } from "vue-router"
import { useI18n } from "vue-i18n"
import { useFeedbackMessages } from "src/use/guiElements"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"
const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})
const { push } = useRouter()
const { t } = useI18n()
const { newStatusMessage } = useFeedbackMessages()
const { createSubmission, v$, saving } = useSubmissionCreation()
const { result } = useQuery(GET_PUBLICATION, props)
const publication = computed(() => result.value?.publication)
async function handleSubmit() {
  try {
    const mutationResult = await createSubmission(publication)
    const submissionId = mutationResult?.data?.createSubmissionDraft?.id
    if (submissionId !== null) {
      push({ name: "submission:draft", params: { id: submissionId } })
    }
  } catch (e) {
    newStatusMessage("failure", t("submissions.create.failure"))
  }
}
</script>

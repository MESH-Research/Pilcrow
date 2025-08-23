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
            params: { id: publication.id }
          }"
        />
        <q-breadcrumbs-el :label="t(`submissions.create.heading`)" />
      </q-breadcrumbs>
    </nav>
    <div class="row flex-center q-pa-lg">
      <div class="col-lg-5 col-md-6 col-sm-8 col-xs-12">
        <article v-if="!publication">
          <q-spinner color="primary" />
        </article>
        <article
          v-else-if="publication.is_accepting_submissions"
          class="q-gutter-md q-pa-lg"
        >
          <h2 class="text-h3 q-mt-lg q-mb-none">
            {{ $t(`submissions.create.heading`) }}
          </h2>
          <p class="q-mt-none q-mb-lg" data-cy="submission_create_subheading">
            {{ publication.name }}
          </p>
          <q-form data-cy="create_submission_form" @submit="handleSubmit()">
            <q-input
              v-model="v$.title.$model"
              :error="v$.title.$error"
              outlined
              :label="t(`submissions.create.title.label`)"
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
                :label="t(`submissions.create.acknowledgement.label`)"
                data-cy="acknowledgement_checkbox"
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
              data-cy="create_submission_btn"
              >{{ $t(`submissions.create.next.btn_label`) }}</q-btn
            >
          </q-form>
        </article>
        <article v-else class="q-px-lg">
          <q-banner class="bg-primary">
            {{ $t(`submissions.create.publication_not_accepting.message`) }}
            <template #action>
              <q-btn
                flat
                color="white"
                :label="
                  $t(`submissions.create.publication_not_accepting.btn_label`)
                "
                :to="{
                  name: 'publication:home',
                  params: { id: publication.id }
                }"
              />
            </template>
          </q-banner>
        </article>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { GET_PUBLICATION } from "src/graphql/queries"
import { useSubmissionCreation } from "src/use/submission"
import { useFeedbackMessages } from "src/use/guiElements"
import ErrorFieldRenderer from "src/components/molecules/ErrorFieldRenderer.vue"

definePage({
  name: "publication:createSubmission"
})

const { params } = useRoute("publication:createSubmission")

const { push } = useRouter()
const { t } = useI18n()
const { newStatusMessage } = useFeedbackMessages({
  attrs: {
    "data-cy": "submission_create_notify"
  }
})
const { createSubmission, v$, saving } = useSubmissionCreation()
const { result } = useQuery(GET_PUBLICATION, params)
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

<template>
  <article v-if="loading" class="q-pa-lg">
    <q-spinner color="primary" />
  </article>
  <div v-else>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el :label="$t('header.publications')" />
        <q-breadcrumbs-el
          :label="
            submission?.publication?.name ??
            $t(`publications.term`, { count: 1 })
          "
        />
        <q-breadcrumbs-el>
          {{
            $t(`submissions.create.draft_title`, {
              submission_title:
                submission?.title ?? $t(`submissions.term`, { count: 1 })
            })
          }}
        </q-breadcrumbs-el>
      </q-breadcrumbs>
    </nav>
    <div class="row flex-center q-pa-md">
      <div class="col-lg-6 col-md-7 col-sm-9 col-xs-12">
        <article
          v-if="submission.status !== 'DRAFT'"
          class="text-center q-py-lg q-px-sm"
        >
          <p>{{ $t(`submissions.create.success`) }}</p>
          <q-btn
            data-cy="visit_submission_btn"
            class="q-mr-sm"
            color="accent"
            size="md"
            :label="
              $t('submissions.accept_invite.update_details.success.action')
            "
            :to="{
              name: 'submission:details',
              params: { id: submission.id }
            }"
          />
        </article>
        <article v-else class="q-py-lg q-px-sm">
          <h1 data-cy="submission_title" class="text-h2 q-ma-none">
            {{ submission.title }}
          </h1>
          <q-chip>
            {{ $t(`submission.status.${submission.status}`) }}
          </q-chip>
          <section class="q-gutter-md q-mt-lg">
            <!-- TODO: Develop metadata updating -->
            <!-- <submission-draft-todo-item title="Submission Information">
              Add metadata associated with your submission.
            </submission-draft-todo-item> -->
            <submission-draft-todo-item
              :done="submission.content !== null"
              :title="$t(`submissions.create.todo.content.title`)"
              :dark-mode="darkModeStatus"
              :required="true"
              @preview-click="onGoToSubmissionPreviewClick"
              @content-click="onGoToSubmissionContentClick"
            >
              <p class="q-ma-none">
                {{ $t(`submissions.create.todo.content.description`) }}
              </p>
            </submission-draft-todo-item>
            <div class="text-h3">
              {{ $t(`submissions.create.custom_form.section_title`) }}
            </div>
            <submission-draft-todo-item
              v-for="form_prompt in submission.publication.meta_forms"
              :key="form_prompt.id"
              :title="form_prompt.name"
              :dark-mode="darkModeStatus"
              :required="form_prompt.required"
              @content-click="onSubmissionMetaClick(form_prompt.id)"
            >
              <p class="q-ma-none">
                {{ form_prompt.caption }}
              </p>
            </submission-draft-todo-item>
            <!-- TODO: Develop collaborator inviting -->
            <!-- <submission-draft-todo-item title="Invite Collaborators">
              Invite collaborators to join the review process.
            </submission-draft-todo-item> -->
          </section>
          <section class="q-mt-lg">
            <p>{{ $t(`submissions.create.submit.description`) }}</p>
            <q-btn
              v-if="draft.content.required.$invalid"
              disabled
              class="q-mt-lg"
              :label="$t(`submissions.create.submit.btn_label`)"
            />
            <q-btn
              v-else
              data-cy="submit_for_review_btn"
              class="q-mt-lg"
              color="primary"
              :label="$t(`submissions.create.submit.btn_label`)"
              @click="confirmHandler('submit_for_review')"
            />
          </section>
        </article>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import ConfirmStatusChangeDialog from "src/components/dialogs/ConfirmStatusChangeDialog.vue"
import SubmissionDraftTodoItem from "src/components/SubmissionDraftTodoItem.vue"
import { GET_SUBMISSION } from "src/graphql/queries"
import { useVuelidate } from "@vuelidate/core"
import { required } from "@vuelidate/validators"
import { useDarkMode } from "src/use/guiElements"

definePage({
  name: "submission:draft"
})

const { params } = useRoute("submission:draft")

const { darkModeStatus } = useDarkMode()

const { dialog } = useQuasar()
const { result, loading } = useQuery(GET_SUBMISSION, params)
const submission = computed(() => result.value?.submission)
const { push } = useRouter()
function onGoToSubmissionPreviewClick() {
  push({
    name: "submission:preview",
    params: { id: submission.value.id }
  })
}
function onGoToSubmissionContentClick() {
  push({
    name: "submission:content",
    params: { id: submission.value.id }
  })
}

function onSubmissionMetaClick(formId) {
  push({
    name: "submission:metaForm",
    params: { id: submission.value.id, formId }
  })
}
const rules = {
  content: {
    required
  }
}
const draft = useVuelidate(rules, submission)
async function confirmHandler(action) {
  await new Promise((resolve) => {
    dirtyDialog(action)
      .onOk(function () {
        resolve(true)
      })
      .onCancel(function () {
        resolve(false)
      })
  })
  {
    return
  }
}
function dirtyDialog(action) {
  return dialog({
    component: ConfirmStatusChangeDialog,
    componentProps: {
      action: action,
      submissionId: params.id,
      currentStatus: submission.value.status
    }
  })
}
</script>

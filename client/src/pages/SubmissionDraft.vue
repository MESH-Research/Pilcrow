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
                submission?.title ?? $t(`submissions.term`, { count: 1 }),
            })
          }}
        </q-breadcrumbs-el>
      </q-breadcrumbs>
    </nav>
    <div class="row flex-center q-pa-lg">
      <div class="col-lg-5 col-md-6 col-sm-8 col-xs-12">
        <article v-if="submission.status !== 'DRAFT'" class="q-pa-lg">
          <p>{{ $t(`submissions.create.success`) }}</p>
          <q-btn
            class="q-mr-sm"
            color="accent"
            size="md"
            :label="
              $t('submissions.accept_invite.update_details.success.action')
            "
            :to="{
              name: 'submission:details',
              params: { id: submission.id },
            }"
          />
        </article>
        <article v-else class="q-pa-lg">
          <h1 data-cy="submission_title" class="text-h2 q-ma-none">
            {{ submission.title }}
          </h1>
          <q-chip>
            {{ $t(`submission.status.${submission.status}`) }}
          </q-chip>
          <h2 class="text-h3 q-mb-lg">
            {{ $t(`submissions.create.todo.heading`) }}
          </h2>
          <section class="q-gutter-md">
            <!-- TODO: Develop metadata updating -->
            <!-- <submission-draft-todo-item title="Update submission details">
              Update the title of your submission as well as enter your
              metadata, etc, etc
            </submission-draft-todo-item> -->
            <submission-draft-todo-item
              :done="submission.content !== null"
              :title="$t(`submissions.create.todo.content.title`)"
              @go-click="onGoToSubmissionContentClick"
            >
              <p class="q-ma-none">
                {{ $t(`submissions.create.todo.content.description`) }}
              </p>
            </submission-draft-todo-item>
            <!-- TODO: Develop collaborator inviting -->
            <!-- <q-banner inline-actions>
              <div>Invite Collaborators</div>
              <div class="text-caption">
                Invite collaborators to join the review process.
              </div>
              <template #action>
                <q-btn flat>Skip</q-btn>
                <q-btn flat> Go </q-btn>
              </template>
            </q-banner> -->
          </section>
          <section class="q-mt-lg">
            <p>{{ $t(`submissions.create.submit.description`) }}</p>
            <q-btn
              :disabled="draft.content.required.$invalid"
              class="q-mt-lg"
              :color="!draft.content.required.$invalid ? 'primary' : ''"
              :label="$t(`submissions.create.submit.btn_label`)"
              @click="confirmHandler('submit_for_review')"
            />
          </section>
        </article>
      </div>
    </div>
  </div>
</template>

<script setup>
import ConfirmStatusChangeDialog from "src/components/dialogs/ConfirmStatusChangeDialog.vue"
import SubmissionDraftTodoItem from "src/components/SubmissionDraftTodoItem.vue"
import { GET_SUBMISSION } from "src/graphql/queries"
import { computed } from "vue"
import { useQuasar } from "quasar"
import { useQuery } from "@vue/apollo-composable"
import { useRouter } from "vue-router"
import { useVuelidate } from "@vuelidate/core"
import { required } from "@vuelidate/validators"

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})
const { dialog } = useQuasar()
const { result, loading } = useQuery(GET_SUBMISSION, props)
const submission = computed(() => result.value?.submission)
const { push } = useRouter()
function onGoToSubmissionContentClick() {
  push({
    name: "submission:content",
    params: { id: submission.value.id },
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
      submissionId: props.id,
    },
  })
}
</script>

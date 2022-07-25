<template>
  <article>
    <h2 class="q-pl-lg">Submissions</h2>
    <div class="row q-col-gutter-lg q-pa-lg">
      <section
        class="col-md-5 col-sm-6 col-xs-12"
        data-cy="create_new_submission_form"
      >
        <h3>Create New Submission</h3>
        <q-form @submit="createNewSubmission()">
          <div class="q-gutter-md column q-pl-none q-pr-md">
            <q-input
              v-model="new_submission.title"
              outlined
              label="Enter Submission Title"
              data-cy="new_submission_title_input"
            />
            <q-select
              v-model="new_submission.publication_id"
              outlined
              :options="publications"
              option-label="name"
              option-value="id"
              emit-value
              map-options
              label="For Publication"
              popup-content-class="publication_options"
              data-cy="new_submission_publication_input"
            />
            <q-file
              v-model="new_submission.file_upload"
              outlined
              label="Upload File"
              multiple
              data-cy="new_submission_file_upload_input"
            >
              <template #prepend>
                <q-icon name="attach_file" />
              </template>
            </q-file>
          </div>
          <q-banner
            v-if="try_catch_error"
            dense
            rounded
            class="form-error text-white bg-negative text-center q-mt-xs"
            data-cy="banner_form_error"
          >
            {{ $t(`submissions.create.failure`) }}
          </q-banner>
          <q-btn
            :disabled="is_submitting"
            class="bg-primary text-white q-mt-lg"
            type="submit"
            data-cy="save_submission"
          >
            Save
          </q-btn>
        </q-form>
      </section>
      <section class="col-md-7 col-sm-6 col-xs-12">
        <h3>All Submissions</h3>
        <q-list
          v-if="submissions.length != 0"
          bordered
          separator
          data-cy="submissions_list"
        >
          <q-item
            v-for="submission in submissions"
            :key="submission.id"
            class="row justify-between"
          >
            <div>
              <router-link
                data-cy="submission_link"
                :to="{
                  name: 'submission_details',
                  params: { id: submission.id },
                }"
              >
                <q-item-label>{{ submission.title }}</q-item-label>
              </router-link>
              <q-item-label caption>
                for {{ submission.publication.name }}
              </q-item-label>
            </div>
            <!-- TODO menu -->
            <div class="q-gutter-sm submission-options">
              <q-btn>
                <q-icon name="more_vert" />
                <q-menu anchor="bottom right" self="top right">
                  <q-item clickable>
                    <q-item-section
                      ><a :href="'submission/review/' + submission.id"
                        >View Submission</a
                      ></q-item-section
                    >
                  </q-item>
                  <q-item clickable>
                    <q-item-section>Change Status</q-item-section>
                    <q-item-section side>
                      <q-icon name="keyboard_arrow_right" />
                    </q-item-section>
                    <q-menu anchor="bottom end" self="top end">
                      <q-item
                        clickable
                        @click="confirmHandler('accept_for_review')"
                        >{{ $t("submission.action.accept_for_review") }}</q-item
                      >
                      <q-item
                        clickable
                        @click="confirmHandler('request_resubmission')"
                        >{{
                          $t("submission.action.request_resubmission")
                        }}</q-item
                      >
                      <q-item clickable @click="confirmHandler('reject')"
                        >{{ $t("submission.action.reject") }}
                      </q-item>

                      <!-- modal dialog here-->
                    </q-menu>
                  </q-item>
                </q-menu>
              </q-btn>
            </div>

            <ul v-if="submission.files.length > 0" class="q-ma-none">
              <li v-for="file in submission.files" :key="file.id">
                <a :href="file.file_upload" download>
                  {{ file.file_upload }}
                </a>
              </li>
            </ul>
          </q-item>
        </q-list>
        <div v-if="subsLoading" class="q-pa-lg">
          {{ $t("loading") }}
        </div>
        <div
          v-else-if="submissions.length == 0"
          data-cy="no_submissions_message"
        >
          No Submissions Created
        </div>
      </section>
    </div>
  </article>
</template>

<script setup>
import { GET_PUBLICATIONS, GET_SUBMISSIONS } from "src/graphql/queries"
import { CREATE_SUBMISSION } from "src/graphql/mutations"
import { required, maxLength } from "@vuelidate/validators"
import { useCurrentUser } from "src/use/user"
import { useFeedbackMessages } from "src/use/guiElements"
import { useI18n } from "vue-i18n"
import { ref, reactive, computed } from "vue"
import { useQuery, useMutation } from "@vue/apollo-composable"
import useVuelidate from "@vuelidate/core"
import ConfirmStatusChangeDialog from "../components/dialogs/ConfirmStatusChangeDialog.vue"
import { useQuasar } from "quasar"

const { dialog } = useQuasar()

const { currentUser } = useCurrentUser()

const is_submitting = ref(false)
const try_catch_error = ref(false)
const new_submission = reactive({
  title: "",
  publication_id: null,
  submitter_user_id: null,
  file_upload: [],
})

//TODO: Implement validation rules a little more DRYly
const rules = {
  title: { required, maxLength: maxLength(512) },
  publication_id: { required },
  submitter_user_id: { required },
  file_upload: { required },
}

const newPubV$ = useVuelidate(rules, new_submission)

const { result: subsResult, loading: subsLoading } = useQuery(GET_SUBMISSIONS)

const submissions = computed(() => {
  return subsResult.value?.submissions.data ?? []
})

const { result: pubsResult } = useQuery(GET_PUBLICATIONS)
const publications = computed(() => {
  return pubsResult.value?.publications.data ?? []
})

const { t } = useI18n()
const { newStatusMessage } = useFeedbackMessages({
  attrs: {
    "data-cy": "create_submission_notify",
  },
})

function checkThatFormIsInvalid() {
  let failureMessage = false
  if (newPubV$.value.title.maxLength.$invalid) {
    failureMessage = "submissions.create.title.max_length"
  } else if (newPubV$.value.title.required.$invalid) {
    failureMessage = "submissions.create.title.required"
  } else if (newPubV$.value.publication_id.required.$invalid) {
    failureMessage = "submissions.create.publication_id.required"
  } else if (newPubV$.value.submitter_user_id.required.$invalid) {
    failureMessage = "submissions.create.submitter_user_id.required"
  } else if (newPubV$.value.file_upload.required.$invalid) {
    failureMessage = "submissions.create.file_upload.required"
  }

  if (failureMessage !== false) {
    newStatusMessage("failure", t(failureMessage))
    return true
  }
  return false
}

const { mutate: createMutate } = useMutation(CREATE_SUBMISSION, {
  context: {
    hasUpload: true,
  },
  refetchQueries: ["GetSubmissions", "currentUserNotifications"],
})

async function createNewSubmission() {
  is_submitting.value = true
  try_catch_error.value = false
  new_submission.submitter_user_id = currentUser.value.id
  if (checkThatFormIsInvalid()) {
    is_submitting.value = false
    return false
  }
  try {
    await createMutate({ ...new_submission })
    newStatusMessage("success", t("submissions.create.success"))
    resetForm()
    is_submitting.value = false
  } catch (error) {
    console.log(error)
    try_catch_error.value = true
    is_submitting.value = false
  }

  function resetForm() {
    Object.assign(new_submission, { title: "", file_upload: [] })
  }
}

async function confirmHandler(action) {
  // fire dialog
  // todo conditional logic
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
function dirtyDialog() {
  return dialog({
    component: ConfirmStatusChangeDialog,
  })
}
</script>

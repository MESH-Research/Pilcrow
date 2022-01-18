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
            v-text="$t(`submissions.create.failure`)"
          />
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
            class="column"
          >
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
            <ul v-if="submission.files.length > 0" class="q-ma-none">
              <li v-for="file in submission.files" :key="file.id">
                <a :href="file.file_upload" download>
                  {{ file.file_upload }}
                </a>
              </li>
            </ul>
          </q-item>
        </q-list>
        <div v-if="$apollo.loading" class="q-pa-lg">
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
import { useQuasar } from "quasar"
import { useI18n } from "vue-i18n"
import { ref, reactive } from "vue"
import { useResult, useQuery, useMutation } from "@vue/apollo-composable"

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
const submissions = useResult(
  useQuery(GET_SUBMISSIONS).result,
  [],
  (data) => data.submissions.data
)
const publications = useResult(
  useQuery(GET_PUBLICATIONS).result,
  [],
  (data) => data.publications.data
)

const { notify } = useQuasar()
const { t } = useI18n()
function makeNotify(color, icon, message) {
  notify({
    color: color,
    icon: icon,
    message: t(message),
    attrs: {
      "data-cy": "create_submission_notify",
    },
    html: true,
  })
  is_submitting.value = false
}
function checkThatFormIsInvalid() {
  if (this.$v.new_submission.title.maxLength.$invalid) {
    makeNotify("negative", "error", "submissions.create.title.max_length")
    return true
  }
  if (this.$v.new_submission.title.required.$invalid) {
    makeNotify("negative", "error", "submissions.create.title.required")
    return true
  }
  if (this.$v.new_submission.publication_id.required.$invalid) {
    makeNotify(
      "negative",
      "error",
      "submissions.create.publication_id.required"
    )
    return true
  }
  if (this.$v.new_submission.submitter_user_id.required.$invalid) {
    makeNotify(
      "negative",
      "error",
      "submissions.create.submitter_user_id.required"
    )
    return true
  }
  if (this.$v.new_submission.file_upload.required.$invalid) {
    makeNotify("negative", "error", "submissions.create.file_upload.required")
    return true
  }
}

const { mutate: createMutate } = useMutation(CREATE_SUBMISSION, {
  context: {
    hasUpload: true,
  },
  refetchQueries: ["GetSubmissions"],
})

async function createNewSubmission() {
  is_submitting.value = true
  try_catch_error.value = false
  console.log(currentUser.value)
  new_submission.submitter_user_id = currentUser.value.id
  //TODO: Form invalid needs to be rewritten to use error slots on the form/inputs
  // if (checkThatFormIsInvalid()) {
  //   return false
  // }
  try {
    await createMutate({ ...new_submission })
    makeNotify("positive", "check_circle", "submissions.create.success")
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
</script>

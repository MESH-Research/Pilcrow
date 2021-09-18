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
              :options="publications.data"
              option-label="name"
              option-value="id"
              emit-value
              map-options
              label="For Publication"
              popup-content-class="publication_options"
              data-cy="new_submission_publication_input"
            />
            <q-file
              v-model="new_submission_files"
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
            v-if="tryCatchError"
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
          v-if="submissions.data.length != 0"
          bordered
          separator
          data-cy="submissions_list"
        >
          <q-item
            v-for="submission in submissions.data"
            :key="submission.id"
            class="column"
          >
            <router-link
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
        <div
          v-if="submissions.data.length == 0"
          data-cy="no_submissions_message"
        >
          No Submissions Created
        </div>
      </section>
    </div>
  </article>
</template>

<script>
import { GET_PUBLICATIONS, GET_SUBMISSIONS } from "src/graphql/queries"
import { CREATE_SUBMISSION } from "src/graphql/mutations"
import useVuelidate from "@vuelidate/core"
import { required, maxLength } from "@vuelidate/validators"

export default {
  setup() {
    return {
      $v: useVuelidate(),
    }
  },
  data() {
    return {
      is_submitting: false,
      tryCatchError: false,
      submissions: {
        data: [],
      },
      publications: {
        data: [],
      },
      new_submission: {
        title: "",
        publication_id: null,
      },
      new_submission_files: [],
    }
  },
  validations() {
    return {
      new_submission: {
        title: { required, maxLength: maxLength(512) },
        publication_id: { required },
      },
      new_submission_files: { required },
    }
  },
  apollo: {
    submissions: {
      query: GET_SUBMISSIONS,
    },
    publications: {
      query: GET_PUBLICATIONS,
    },
  },
  methods: {
    makeNotify(color, icon, message) {
      this.$q.notify({
        color: color,
        icon: icon,
        message: this.$t(message),
        attrs: {
          "data-cy": "create_submission_notify",
        },
        html: true,
      })
      this.is_submitting = false
    },
    checkThatFormIsInvalid() {
      if (this.$v.new_submission.title.maxLength.$invalid) {
        this.makeNotify(
          "negative",
          "error",
          "submissions.create.title.max_length"
        )
        return true
      }
      if (this.$v.new_submission.title.required.$invalid) {
        this.makeNotify(
          "negative",
          "error",
          "submissions.create.title.required"
        )
        return true
      }
      if (this.$v.new_submission.publication_id.required.$invalid) {
        this.makeNotify(
          "negative",
          "error",
          "submissions.create.publication_id.required"
        )
        return true
      }
      if (this.$v.new_submission_files.required.$invalid) {
        this.makeNotify(
          "negative",
          "error",
          "submissions.create.file_upload.required"
        )
        return true
      }
    },
    async createNewSubmission() {
      this.is_submitting = true
      this.tryCatchError = false
      if (this.checkThatFormIsInvalid()) {
        return false
      }
      try {
        await this.$apollo.mutate({
          mutation: CREATE_SUBMISSION,
          variables: this.new_submission,
          context: {
            hasUpload: true,
          },
          refetchQueries: ["GetSubmissions"], // Refetch queries since the result is paginated.
        })
        this.makeNotify(
          "positive",
          "check_circle",
          "submissions.create.success"
        )
        this.new_submission.title = ""
        this.new_submission_files = []
        this.is_submitting = false
      } catch (error) {
        console.log(error)
        this.tryCatchError = true
        this.is_submitting = false
      }
    },
  },
}
</script>

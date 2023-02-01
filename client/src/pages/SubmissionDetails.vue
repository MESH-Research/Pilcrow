<template>
  <div v-if="!submission" class="q-pa-lg">
    {{ $t("loading") }}
  </div>
  <article v-else>
    <nav class="q-px-lg q-pt-md q-gutter-sm">
      <q-breadcrumbs>
        <q-breadcrumbs-el
          :label="$t('header.submissions', 2)"
          to="/submissions"
        />
        <q-breadcrumbs-el :label="$t('submissions.details_heading')" />
      </q-breadcrumbs>
    </nav>
    <section class="row no-wrap items-center q-px-lg q-pt-md">
      <h2
        v-if="!editing_title"
        class="cursor-pointer"
        title="Edit the Title"
        @click="editTitle"
      >
        {{ submission.title }}
      </h2>
      <q-btn
        v-if="!editing_title"
        flat
        icon="edit"
        color="accent"
        class="q-ml-sm"
        size="sm"
        padding="sm"
        :aria-label="$t('submission.action.edit_title')"
        @click="editTitle"
      >
        <q-tooltip anchor="center right" self="center left">{{
          $t("submission.edit_title.tooltip")
        }}</q-tooltip>
      </q-btn>
      <q-form
        v-if="editing_title"
        class="col large-text-inputs"
        @submit.prevent="saveTitle"
      >
        <q-input
          v-model="draft_title"
          autofocus
          class="text-h2"
          :label="$t(`submission.edit_title.set_title`)"
          input-class="q-py-xl"
          outlined
          :placeholder="$t(`submission.edit_title.placeholder`)"
        />
        <div class="q-mt-sm">
          <q-btn
            type="submit"
            :label="$t(`buttons.save`)"
            color="positive"
            :loading="submitting_title_edit"
          >
            <template #loading>
              <q-spinner color="primary" />
            </template>
          </q-btn>
          <q-btn
            :label="$t(`guiElements.form.cancel`)"
            flat
            class="q-ml-sm"
            @click="cancelEditTitle"
          />
        </div>
      </q-form>
    </section>
    <section>
      <q-banner class="light-grey">
        <div class="flex row items-center">
          <h3 class="q-ml-sm q-mr-md text-h4">
            {{ $t("submission.status.title") }}
          </h3>
          <q-separator vertical />
          <q-chip
            class="q-ml-md"
            icon="radio_button_checked"
            color="primary"
            text-color="white"
          >
            {{ $t(`submission.status.${submission.status}`) }}
          </q-chip>
          <q-space />
          <q-btn
            data-cy="submission_review_btn"
            class="q-mr-sm"
            color="accent"
            size="lg"
            :label="$t('submissions.action.review.name')"
            :to="{
              name: 'submission_review',
              params: { id: props.id },
            }"
          />
        </div>
      </q-banner>
    </section>
    <section class="q-pa-lg">
      <div class="row q-col-gutter-lg">
        <div class="col-12 col-xs-8 col-sm-5 col-md-3">
          <assigned-submission-users
            data-cy="submitters_list"
            relationship="submitters"
            :container="submission"
          />
        </div>

        <div
          :class="{
            'q-mt-lg': $q.screen.width < 600,
          }"
          class="col-12 col-xs-8 col-sm-5 col-md-3"
        >
          <assigned-submission-users
            relationship="review_coordinators"
            data-cy="coordinators_list"
            :container="submission"
            mutable
            :max-users="1"
          />
        </div>
      </div>
      <div class="row q-col-gutter-lg q-mt-lg">
        <div class="col-12 col-xs-8 col-sm-10 col-md-6">
          <assigned-submission-users
            data-cy="reviewers_list"
            relationship="reviewers"
            :container="submission"
            mutable
          />
        </div>
      </div>
    </section>
    <section class="q-pa-lg" data-cy="activity_section">
      <h3>{{ $t("submission.activity_section.title") }}</h3>
      <p v-if="submission.audits.length == 0">
        {{ $t("submission.activity_section.no_activity") }}
      </p>
      <submission-audit
        v-for="audit in submission.audits.slice().reverse()"
        :key="audit.id"
        :audit="audit"
      />
    </section>
  </article>
</template>

<script setup>
import { GET_SUBMISSION } from "src/graphql/queries"
import { UPDATE_SUBMISSION_TITLE } from "src/graphql/mutations"
import AssignedSubmissionUsers from "src/components/AssignedSubmissionUsers.vue"
import { useQuery } from "@vue/apollo-composable"
import { useMutation } from "@vue/apollo-composable"
import { computed, ref, watchEffect } from "vue"
import SubmissionAudit from "../components/SubmissionAudit.vue"
import { required, maxLength } from "@vuelidate/validators"
import useVuelidate from "@vuelidate/core"
import { useFeedbackMessages } from "src/use/guiElements"
import { useI18n } from "vue-i18n"
const { t } = useI18n()
const { newStatusMessage } = useFeedbackMessages({
  attrs: {
    "data-cy": "create_submission_notify",
  },
})
const draft_title = ref("")
const rules = {
  required,
  maxLength: maxLength(512),
}
const newPubV$ = useVuelidate(rules, draft_title)
function checkThatFormIsInvalid() {
  let failureMessage = false

  if (newPubV$.value.required.$invalid) {
    failureMessage = "submissions.create.title.required"
    draft_title.value = submission.value.title
  } else if (newPubV$.value.maxLength.$invalid) {
    failureMessage = "submissions.create.title.max_length"
  }
  if (failureMessage !== false) {
    newStatusMessage("failure", t(failureMessage))
    return true
  }
  return false
}

const props = defineProps({
  id: {
    type: String,
    required: true,
  },
})

const { result } = useQuery(GET_SUBMISSION, { id: props.id })
const submission = computed(() => {
  return result.value?.submission
})

watchEffect(() => {
  if (submission.value) {
    draft_title.value = submission.value.title
  }
})

const { mutate } = useMutation(UPDATE_SUBMISSION_TITLE)
const editing_title = ref(false)
const submitting_title_edit = ref(false)
function editTitle() {
  editing_title.value = true
}
function cancelEditTitle() {
  editing_title.value = false
}
async function saveTitle() {
  submitting_title_edit.value = true
  if (checkThatFormIsInvalid()) {
    submitting_title_edit.value = false
    return false
  }
  try {
    await mutate({
      id: submission.value.id,
      title: draft_title.value,
    })
    editing_title.value = false
    submitting_title_edit.value = false
  } catch (error) {
    console.log(error)
  }
}
</script>

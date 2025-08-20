<template>
  <h2
    v-if="!editing_title"
    data-cy="submission_title"
    class="cursor-pointer"
    :title="$t(`submission.edit_title.tooltip`)"
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
    :aria-label="$t('submission.edit_title.tooltip')"
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
      data-cy="submission_title_input"
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
</template>

<script setup lang="ts">
import { UPDATE_SUBMISSION_TITLE } from "src/graphql/mutations"
import { useMutation } from "@vue/apollo-composable"
import { ref, watchEffect, inject } from "vue"
import { useI18n } from "vue-i18n"
import { useFeedbackMessages } from "src/use/guiElements"
import { required, maxLength } from "@vuelidate/validators"
import useVuelidate from "@vuelidate/core"

const submission = inject("submission")
const draft_title = ref("")

watchEffect(() => {
  if (submission.value) {
    draft_title.value = submission.value.title
  }
})

const { t } = useI18n()

const rules = {
  required,
  maxLength: maxLength(512)
}
const newPubV$ = useVuelidate(rules, draft_title)

const { newStatusMessage } = useFeedbackMessages({
  attrs: {
    "data-cy": "submission_title_notify"
  }
})

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

const { mutate } = useMutation(UPDATE_SUBMISSION_TITLE, {
  refetchQueries: ["GetSubmission"]
})
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
      title: draft_title.value
    })
  } catch (error) {
    newStatusMessage("failure", t("submission.edit_title.unauthorized"))
  } finally {
    editing_title.value = false
    submitting_title_edit.value = false
  }
}
</script>

<template>
  <q-btn
    data-cy="submission_actions"
    :aria-label="$t(`submissions.action.toggle_label`)"
  >
    <q-icon name="more_vert" />
    <q-menu anchor="bottom right" self="top right">
      <q-item
        v-if="submission.status === 'DRAFT'"
        clickable
        :disable="cannotAccessSubmission(submission)"
        data-cy="submission_draft_link"
        :to="{
          name: 'submission:draft',
          params: { id: props.submission.id },
        }"
      >
        <q-item-section>
          <q-item-label>
            {{ $t("submissions.action.draft") }}
          </q-item-label>
        </q-item-section>
      </q-item>
      <q-item
        v-if="submission.status !== 'DRAFT'"
        clickable
        :disable="cannotAccessSubmission(submission)"
        data-cy="submission_review_link"
        :to="{
          name: 'submission:review',
          params: { id: props.submission.id },
        }"
      >
        <q-item-section>
          <q-item-label>
            {{ $t("submissions.action.review.label") }}
          </q-item-label>
        </q-item-section>
        <q-tooltip
          v-if="cannotAccessSubmission(submission)"
          anchor="top middle"
          self="bottom middle"
          data-cy="cannot_access_submission_tooltip"
        >
          {{ $t("submissions.action.review.no_access") }}
        </q-tooltip>
      </q-item>
      <q-item
        v-if="submission.status !== 'DRAFT'"
        clickable
        :disable="cannotAccessSubmission(submission)"
        data-cy="submission_details_link"
        :to="{
          name: 'submission:details',
          params: { id: props.submission.id },
        }"
      >
        <q-item-section>
          <q-item-label>
            {{ $t("submissions.action.view_details.label") }}
          </q-item-label>
        </q-item-section>
        <q-tooltip
          v-if="cannotAccessSubmission(submission)"
          anchor="top middle"
          self="bottom middle"
          data-cy="cannot_access_submission_tooltip"
        >
          {{ $t("submissions.action.view_details.no_access") }}
        </q-tooltip>
      </q-item>
      <q-item
        v-if="!statusChangingDisabledByRole"
        data-cy="change_status"
        clickable
        :disable="statusChangingDisabledByState"
      >
        <q-item-section data-cy="change_status_item_section">
          <q-item-label>
            {{ $t("submissions.action.change_status.label") }}
          </q-item-label>
        </q-item-section>
        <q-tooltip
          v-if="statusChangingDisabledByState"
          anchor="top middle"
          self="bottom middle"
          :offset="[10, 10]"
          data-cy="cannot_change_submission_status_tooltip"
        >
          {{
            $t(
              `submissions.action.change_status.no_access.${submission.status}`,
            )
          }}
        </q-tooltip>

        <q-item-section side>
          <q-icon color="accent" name="keyboard_arrow_right" />
        </q-item-section>
        <q-menu
          anchor="bottom end"
          self="top end"
          data-cy="change_status_dropdown"
        >
          <div v-if="submission.status == 'DRAFT'">
            <q-item
              data-cy="initially_submit"
              class="items-center"
              clickable
              @click="confirmHandler('submit_for_review', submission.id)"
              >{{ $t("submission.action.submit_for_review") }}</q-item
            >
          </div>
          <div
            v-else-if="
              submission.status != 'AWAITING_REVIEW' &&
              submission.status != 'REJECTED' &&
              submission.status != 'RESUBMISSION_REQUESTED' &&
              submission.status != 'DELETED'
            "
          >
            <q-item
              v-if="submission.status == 'INITIALLY_SUBMITTED'"
              data-cy="accept_for_review"
              class="items-center"
              clickable
              @click="confirmHandler('accept_for_review', submission.id)"
              >{{ $t("submission.action.accept_for_review") }}</q-item
            >
            <q-item
              v-if="
                submission.status != 'INITIALLY_SUBMITTED' &&
                submission.status != 'ACCEPTED_AS_FINAL' &&
                submission.status != 'ARCHIVED'
              "
              data-cy="accept_as_final"
              class="items-center"
              clickable
              @click="confirmHandler('accept_as_final', submission.id)"
              >{{ $t("submission.action.accept_as_final") }}</q-item
            >
            <q-item
              v-if="
                submission.status != 'ACCEPTED_AS_FINAL' &&
                submission.status != 'ARCHIVED'
              "
              class="items-center"
              clickable
              @click="confirmHandler('request_resubmission', submission.id)"
              >{{ $t("submission.action.request_resubmission") }}</q-item
            >
            <q-item
              v-if="
                submission.status != 'ACCEPTED_AS_FINAL' &&
                submission.status != 'ARCHIVED'
              "
              data-cy="reject"
              class="items-center"
              clickable
              @click="confirmHandler('reject', submission.id)"
              >{{ $t("submission.action.reject") }}
            </q-item>
            <q-item
              v-if="submission.status == 'ACCEPTED_AS_FINAL'"
              data-cy="archive"
              class="items-center"
              clickable
              @click="confirmHandler('archive', submission.id)"
              >{{ $t("submission.action.archive") }}
            </q-item>
            <q-item
              v-if="
                submission.status == 'ACCEPTED_AS_FINAL' ||
                submission.status == 'ARCHIVED'
              "
              data-cy="delete"
              class="items-center"
              clickable
              @click="confirmHandler('delete', submission.id)"
              >{{ $t("submission.action.delete") }}
            </q-item>
          </div>
          <q-separator />
          <q-item
            v-if="submission.status == 'AWAITING_REVIEW'"
            data-cy="open_review"
            class="items-center"
            clickable
            @click="confirmHandler('open', submission.id)"
            >{{ $t("submission.action.open") }}
          </q-item>
          <q-item
            v-if="submission.status == 'UNDER_REVIEW'"
            data-cy="close_review"
            class="items-center"
            clickable
            @click="confirmHandler('close', submission.id)"
            >{{ $t("submission.action.close") }}
          </q-item>
        </q-menu>
      </q-item>
      <q-item
        :disable="isDisabledByRole || isDisabledByState"
        data-cy="export_submission"
        clickable
        :to="{
          name: 'submission:export',
          params: { id: submission.id },
        }"
      >
        <q-item-section>
          <q-item-label>
            {{ $t(`export.call_to_action`) }}
          </q-item-label>
        </q-item-section>
        <q-tooltip
          v-if="isDisabledByRole"
          anchor="top middle"
          self="bottom middle"
          :offset="[10, 10]"
          data-cy="cannot_export_submission_tooltip"
        >
          {{ $t(`export.disabled.by_role`) }}
        </q-tooltip>
        <q-tooltip
          v-else-if="isDisabledByState"
          anchor="top middle"
          self="bottom middle"
          :offset="[10, 10]"
          data-cy="cannot_export_submission_tooltip"
        >
          {{ $t(`export.disabled.by_state`) }}
        </q-tooltip>
      </q-item>
    </q-menu>
  </q-btn>
</template>

<script setup>
import ConfirmStatusChangeDialog from "../components/dialogs/ConfirmStatusChangeDialog.vue"
import { useQuasar } from "quasar"
import {
  useSubmissionExport,
  useStatusChangeControls,
} from "src/use/guiElements.js"
import { ref } from "vue"
const { dialog } = useQuasar()

const submissionRef = ref(props.submission)
const { isDisabledByRole, isDisabledByState } =
  useSubmissionExport(submissionRef)
const { statusChangingDisabledByRole, statusChangingDisabledByState } =
  useStatusChangeControls(submissionRef)

const props = defineProps({
  submission: {
    type: Object,
    default: () => {},
  },
  actionType: {
    type: String,
    default: "",
  },
})

function cannotAccessSubmission(submission) {
  const nonreviewableStates = new Set([
    "DRAFT",
    "INITIALLY_SUBMITTED",
    "REJECTED",
    "RESUBMISSION_REQUESTED",
  ])
  return (
    nonreviewableStates.has(submission.status) &&
    submission.my_role == "reviewer" &&
    submission.effective_role == "reviewer"
  )
}
async function confirmHandler(action, id) {
  await new Promise((resolve) => {
    dirtyDialog(action, id)
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
function dirtyDialog(action, id) {
  return dialog({
    component: ConfirmStatusChangeDialog,
    componentProps: {
      action: action,
      submissionId: id,
    },
  })
}
</script>

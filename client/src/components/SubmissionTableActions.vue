<template>
  <q-btn
    data-cy="submission_actions"
    aria-label="{{$t(submissions.action.toggle_label)}}"
  >
    <q-icon name="more_vert" />
    <q-menu anchor="bottom right" self="top right">
      <q-item
        clickable
        :disable="cannotAccessSubmission(submission)"
        data-cy="review"
        :to="destination_to"
      >
        <q-item-section>
          <q-item-label v-if="actionType == 'reviews'">
            {{ $t("submissions.details_heading") }}
          </q-item-label>
          <q-item-label v-else>
            {{ $t("submissions.action.review.name") }}
          </q-item-label>
        </q-item-section>
        <q-tooltip
          v-if="cannotAccessSubmission(submission)"
          anchor="top middle"
          self="bottom middle"
          class="text-body1"
          data-cy="cannot_access_submission_tooltip"
        >
          {{ $t("submissions.action.review.no_access") }}
        </q-tooltip>
      </q-item>
      <q-item
        data-cy="change_status"
        clickable
        :disable="
          submission.status == 'REJECTED' ||
          submission.status == 'RESUBMISSION_REQUESTED'
        "
      >
        <q-item-section data-cy="change_status_item_section">
          <q-item-label>
            {{ $t("submissions.action.change_status.name") }}
          </q-item-label>
        </q-item-section>
        <q-tooltip
          v-if="
            submission.status == 'REJECTED' ||
            submission.status == 'RESUBMISSION_REQUESTED'
          "
          anchor="top middle"
          self="bottom middle"
          :offset="[10, 10]"
          class="text-body1"
          data-cy="cannot_change_submission_status_tooltip"
        >
          {{
            $t(
              `submissions.action.change_status.no_access.${submission.status}`
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
              submission.status != 'RESUBMISSION_REQUESTED'
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
              v-if="submission.status != 'INITIALLY_SUBMITTED'"
              data-cy="accept_as_final"
              class="items-center"
              clickable
              @click="confirmHandler('accept_as_final', submission.id)"
              >{{ $t("submission.action.accept_as_final") }}</q-item
            >
            <q-item
              class="items-center"
              clickable
              @click="confirmHandler('request_resubmission', submission.id)"
              >{{ $t("submission.action.request_resubmission") }}</q-item
            >
            <q-item
              class="items-center"
              clickable
              @click="confirmHandler('reject', submission.id)"
              >{{ $t("submission.action.reject") }}
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
        v-if="isExportVisible"
        :disable="!isExportEnabled"
        data-cy="export_submission"
        clickable
        :to="{
          name: 'submission_export',
          params: { id: submission.id },
        }"
      >
        <q-item-section>
          <q-item-label>
            {{ $t(`export.call_to_action`) }}
          </q-item-label>
        </q-item-section>
      </q-item>
    </q-menu>
  </q-btn>
</template>

<script setup>
import ConfirmStatusChangeDialog from "../components/dialogs/ConfirmStatusChangeDialog.vue"
import { useQuasar } from "quasar"
import { computed } from "vue"
import { useSubmissionExport } from "src/use/guiElements.js"
import { ref } from "vue"
const { dialog } = useQuasar()

const submissionRef = ref(props.submission)
const { isExportVisible, isExportEnabled } = useSubmissionExport(submissionRef.value)

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

const destination_to = computed(() => {
  let to = {
    name: "submission_review",
    params: { id: props.submission.id },
  }
  if (props.actionType == "reviews") {
    to = {
      name: "submission_details",
      params: { id: props.submission.id },
    }
  }
  return to
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

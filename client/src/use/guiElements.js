import { computed, ref, watch } from "vue"
import { useQuasar } from "quasar"
import { useI18n } from "vue-i18n"
import { useCurrentUser } from "./user"

export function useDarkMode() {
  const $q = useQuasar()
  const darkModeStatus = ref($q.dark.isActive)
  watch(
    () => $q.dark.isActive,
    () => {
      darkModeStatus.value = $q.dark.isActive
    },
  )
  function toggleDarkMode() {
    $q.dark.toggle()
  }
  return { darkModeStatus, toggleDarkMode }
}

/**
 * Display feedback messages to the user using the Quasar notify plugin.
 *
 * @param   {Object}  overrideDefaults  Override default options for message functions
 */
export function useFeedbackMessages(overrideDefaults = {}) {
  const { t } = useI18n()
  const { notify } = useQuasar()

  const defaultOptions = Object.assign(overrideDefaults, {
    group: false,
    actions: [
      {
        label: t("guiElements.feedbackMessage.closeButton"),
        color: "white",
        "data-cy": "button_dismiss_notify",
      },
    ],
    timeout: 10000,
    progress: true,
    html: true,
  })

  /**
   * Show a new feedback message to the user.
   *
   * @param   {string}  message  Message content
   * @param   {Object}  opts     Override default options
   */
  function newMessage(message, opts) {
    const options = Object.assign({ message, ...opts }, defaultOptions)

    notify(options)
  }

  /**
   * Show a status (success or failure) feedback message to the user.
   *
   * @param   {string}  status   One of: failure, success
   * @param   {string}  message  Message content
   */
  function newStatusMessage(status, message) {
    const type = {
      success: "positive",
      failure: "negative",
    }[status]
    newMessage(message, { type })
  }

  return { newMessage, newStatusMessage }
}

export const submissionStateButtons = {
  DRAFT: {
    action: null,
    attrs: {
      color: "",
      class: "",
      "data-cy": "",
    },
  },
  INITIALLY_SUBMITTED: {
    action: "submit_for_review",
    attrs: {
      color: "positive",
      class: "",
      "data-cy": "initially_submit",
    },
    icon: "edit_document",
  },
  AWAITING_REVIEW: {
    action: "accept_for_review",
    attrs: {
      color: "positive",
      class: "",
      "data-cy": "open_for_review",
    },
    icon: "done",
  },
  UNDER_REVIEW: {
    action: "open",
    attrs: {
      color: "black",
      class: "",
      "data-cy": "open_for_review",
    },
    icon: "grading",
  },
  AWAITING_DECISION: {
    action: "close",
    attrs: {
      color: "black",
      class: "",
      "data-cy": "close_for_review",
    },
    icon: "grading",
  },
  ACCEPTED_AS_FINAL: {
    action: "accept_as_final",
    attrs: {
      color: "positive",
      class: "",
      "data-cy": "accept_as_final",
    },
    icon: "done",
  },
  ARCHIVED: {
    action: "archive",
    attrs: {
      color: "dark-grey",
      class: "",
      "data-cy": "archive",
    },
    icon: "archive",
  },
  DELETED: {
    action: "delete",
    attrs: {
      color: "negative",
      class: "",
      "data-cy": "delete",
    },
    icon: "delete",
  },
  REJECTED: {
    action: "reject",
    attrs: {
      color: "negative",
      class: "",
      "data-cy": "",
    },
    icon: "do_not_disturb",
  },
  RESUBMISSION_REQUESTED: {
    action: "request_resubmission",
    attrs: {
      color: "dark-grey",
      class: "text-white request-resubmission",
      "data-cy": "",
    },
    icon: "refresh",
  },
  EXPIRED: {
    action: null,
    attrs: {
      color: "",
      class: "",
      "data-cy": "",
    },
    icon: "",
  },
}

export function useStatusChangeControls(submission) {
  const { isReviewer } = useCurrentUser()

  const statusChangingDisabledByRole = computed(() => {
    if (!submission.value) {
      return true
    }
    return isReviewer(submission.value)
  })

  const statusChangingDisabledStates = [
    "REJECTED",
    "RESUBMISSION_REQUESTED",
    "DELETED",
  ]

  const statusChangingDisabledByState = computed(() => {
    if (!submission.value) {
      return true
    }
    return statusChangingDisabledStates.includes(submission.value.status)
  })

  const nextStates = {
    DRAFT: ["INITIALLY_SUBMITTED"],
    INITIALLY_SUBMITTED: [
      "UNDER_REVIEW",
      "ACCEPTED_AS_FINAL",
      "RESUBMISSION_REQUESTED",
      "REJECTED",
    ],
    AWAITING_REVIEW: ["UNDER_REVIEW"],
    UNDER_REVIEW: [
      "ACCEPTED_AS_FINAL",
      "RESUBMISSION_REQUESTED",
      "REJECTED",
      "AWAITING_DECISION",
    ],
    AWAITING_DECISION: [
      "ACCEPTED_AS_FINAL",
      "RESUBMISSION_REQUESTED",
      "REJECTED",
    ],
    ACCEPTED_AS_FINAL: ["ARCHIVED", "DELETED"],
    RESUBMISSION_REQUESTED: ["ARCHIVED", "DELETED"],
    REJECTED: ["ARCHIVED", "DELETED"],
    ARCHIVED: ["DELETED"],
    DELETED: [],
    EXPIRED: ["ACCEPTED_AS_FINAL", "RESUBMISSION_REQUESTED", "REJECTED"],
  }

  return {
    nextStates,
    statusChangingDisabledByRole,
    statusChangingDisabledByState,
  }
}

export function useSubmissionExport(submission) {
  const {
    isAppAdmin,
    isPublicationAdmin,
    isEditor,
    isReviewCoordinator,
    isSubmitter,
  } = useCurrentUser()

  const exportVisibleStates = [
    "REJECTED",
    "RESUBMISSION_REQUESTED",
    "ACCEPTED_AS_FINAL",
    "ARCHIVED",
    "EXPIRED",
  ]
  const isDisabledByRole = computed(() => {
    if (!submission.value) {
      return true
    }
    return !(
      isAppAdmin.value ||
      isPublicationAdmin(submission.value.publication) ||
      isEditor(submission.value.publication) ||
      isReviewCoordinator(submission.value) ||
      isSubmitter(submission.value)
    )
  })
  const isDisabledByState = computed(() => {
    if (!submission.value) {
      return true
    }
    return !exportVisibleStates.includes(submission.value.status)
  })

  return { isDisabledByRole, isDisabledByState }
}

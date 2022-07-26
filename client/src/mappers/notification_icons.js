import { flatten } from "flat"

const icons = flatten({
  submission: {
    initially_submitted: "content_copy",
    resubmitted: "redo",
    resubmission_requested: "undo",
    accepted_for_review: "task_alt",
    rejected_for_review: "cancel",
    accepted_as_final: "task_alt",
    expired: "disabled_by_default",
    under_review: "start",
    awaiting_decision: "schedule",
    awaiting_revision: "undo",
    archived: "inventory_2",
    deleted: "delete",
  },
})

export default function getIcon(type) {
  return icons[type] ?? "help"
}

import { flatten } from "flat"

const icons = flatten({
  submission: {
    initially_submitted: "content_copy",
    resubmitted: "redo",
    resubmission_requested: "undo",
    awaiting_review: "task_alt",
    rejected: "cancel",
    accepted_as_final: "task_alt",
    expired: "disabled_by_default",
    under_review: "start",
    awaiting_decision: "schedule",
    revision_requested: "undo",
    archived: "inventory_2",
    deleted: "delete",
  },
})

export default function getIcon(type) {
  return icons[type] ?? "help"
}

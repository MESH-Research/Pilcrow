const colors = {
  admin_todo: "bg-amber-1",
  user_todo: "bg-deep-orange-1",
  in_process: "bg-teal-1",
  draft: "bg-grey-1",
  complete: "bg-green-1",
  removed: "bg-grey-1"
}
export const styles = {
  submission_status: {
    draft: {
      icon: "edit",
      bgColor: colors.draft
    },
    initially_submitted: {
      icon: "mail",
      bgColor: colors.admin_todo
    },
    resubmission_requested: {
      bgColor: colors.user_todo,
      icon: "pending"
    },
    revision_requested: {
      icon: "mdi-refresh",
      bgColor: colors.user_todo
    },
    awaiting_review: {
      icon: "mdi-clock-outline",
      bgColor: colors.admin_todo
    },
    rejected: {
      icon: "mdi-close-circle",
      bgColor: colors.removed
    },
    accepted_as_final: {
      icon: "mdi-check-circle",
      bgColor: colors.complete
    },
    expired: {
      icon: "mdi-timer-off",
      bgColor: colors.removed
    },
    under_review: {
      icon: "mdi-eye",
      bgColor: colors.in_process
    },
    awaiting_decision: {
      icon: "mdi-check-all",
      bgColor: colors.admin_todo
    },
    archived: {
      icon: "mdi-archive",
      bgColor: colors.removed
    },
    deleted: {
      icon: "mdi-delete",
      bgColor: colors.removed
    }
  }
}

const def = {
  icon: "mdi-help-circle",
  color: "grey",
  bgColor: "transparent"
}

export function getStyle(status) {
  return Object.assign(def, styles.submission_status[status.toLowerCase()])
}

export function getIcon(status) {
  const style = getStyle(status)
  return style.icon
}

export function getColor(status) {
  const style = getStyle(status)
  return style.color
}

export function getBgColor(status) {
  const style = getStyle(status)
  return style.bgColor
}

export function getClasses(status) {
  const style = getStyle(status)
  return [style.bgColor, style.color]
}

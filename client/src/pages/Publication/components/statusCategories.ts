export interface StatusCategoryDef {
  key: string
  color: string
  textClass: string
  icon: string
  pattern: string
  statuses: string[]
}

export const statusCategories: StatusCategoryDef[] = [
  {
    key: "needs_action",
    color: "warning",
    textClass: "text-dark",
    icon: "flag",
    pattern: "pattern-diagonal",
    statuses: [
      "INITIALLY_SUBMITTED",
      "RESUBMITTED",
      "AWAITING_REVIEW",
      "AWAITING_DECISION"
    ]
  },
  {
    key: "in_progress",
    color: "info",
    textClass: "text-dark",
    icon: "hourglass_top",
    pattern: "pattern-zigzag",
    statuses: ["UNDER_REVIEW"]
  },
  {
    key: "awaiting_author",
    color: "secondary",
    textClass: "text-white",
    icon: "edit_note",
    pattern: "pattern-dots",
    // DRAFT intentionally omitted — drafts are the author's private
    // work-in-progress and aren't visible from the publication dashboard
    // until the author submits.
    statuses: ["RESUBMISSION_REQUESTED", "REVISION_REQUESTED"]
  },
  {
    key: "completed",
    color: "dashboard-green",
    textClass: "text-white",
    icon: "check_circle",
    pattern: "pattern-crosshatch",
    statuses: [
      "ACCEPTED_AS_FINAL",
      "REJECTED",
      "EXPIRED",
      "ARCHIVED",
      "DELETED"
    ]
  }
]

/** Map from status string to its category style. */
export const statusStyleMap: Record<
  string,
  { color: string; textClass: string; icon: string; pattern: string }
> = Object.fromEntries(
  statusCategories.flatMap((cat) =>
    cat.statuses.map((s) => [
      s,
      {
        color: cat.color,
        textClass: cat.textClass,
        icon: cat.icon,
        pattern: cat.pattern
      }
    ])
  )
)

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
    // Terminal / paused states — nothing for the dashboard admin to
    // act on. RESUBMISSION_REQUESTED and REVISION_REQUESTED are
    // parked here because they're closed from the publication's
    // side: work sits with the author until they resubmit. DRAFT is
    // intentionally omitted — drafts are the author's private
    // work-in-progress and aren't visible to the dashboard.
    // A green checkmark would read as "successfully done" which is
    // misleading for REJECTED / EXPIRED / DELETED, so the category
    // uses a neutral slate + done_outline instead of affirmative
    // green + check_circle.
    key: "completed",
    color: "blue-grey-7",
    textClass: "text-white",
    icon: "done_outline",
    pattern: "pattern-crosshatch",
    statuses: [
      "RESUBMISSION_REQUESTED",
      "REVISION_REQUESTED",
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

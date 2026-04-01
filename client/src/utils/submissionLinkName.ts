interface Submission {
  status: string
}
interface SubmissionStatuses {
  [status: string]: string
}
export const submissionLinkName = (submission: Submission, role: string) => {
  let name = "submission:review"
  const states: SubmissionStatuses = {
    "DRAFT": "submission:draft",
    "INITIALLY_SUBMITTED": "submission:view",
    "RESUBMISSION_REQUESTED": "submission:view",
    "RESUBMITTED": "submission:view",
    "AWAITING_REVIEW": "submission:view",
    "REJECTED": "submission:view",
  }
  if (!submission?.status) {
    name = states[submission.status]
  }
  if (role && role !== "submitter" && submission?.status === "DRAFT") {
    name = "submission:preview"
  }

  return name
}

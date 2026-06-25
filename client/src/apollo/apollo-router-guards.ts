import { SessionStorage } from "quasar"
import gql from "graphql-tag"
import { CURRENT_USER, CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"

/**
 * Colocated query for the route guards' unassigned-access fallback. It selects
 * only the submission ability flags the guards need, so it stays decoupled from
 * the page-level GET_SUBMISSION query and can't pull that operation's heavier
 * field set (or be perturbed by it).
 */
const GUARD_SUBMISSION_ACCESS = gql`
  query GuardSubmissionAccess($id: ID!) {
    submission(id: $id) {
      id
      abilities {
        view
        export
      }
    }
  }
`

/**
 * Resolve a submission's authorization flags for the viewer. Used as the
 * fallback for users not directly assigned to a submission (so it never appears
 * in their submissions list) who may still reach it through a publication role:
 * the server grants publication admins, editors and the application
 * administrator the scoped `view`/`export` abilities directly on the submission,
 * resolved through the same engine as the policies.
 *
 * Returns null when the submission cannot be fetched (no access), which the
 * callers treat as "denied".
 */
async function fetchSubmission(apolloClient, submissionId) {
  try {
    return await apolloClient
      .query({
        query: GUARD_SUBMISSION_ACCESS,
        variables: { id: submissionId }
      })
      .then(({ data: { submission } }) => submission)
  } catch (error) {
    return null
  }
}

export async function beforeEachRequiresAuth(apolloClient, to, _, next) {
  if (to.matched.some((record) => record.meta.requiresAuth)) {
    const user = await apolloClient
      .query({
        query: CURRENT_USER
      })
      .then(({ data: { currentUser } }) => currentUser)
    if (!user) {
      SessionStorage.set("loginRedirect", to.fullPath)
      next("/login")
    } else {
      next()
    }
  } else {
    next()
  }
}

export async function beforeEachRequiresSubmissionAccess(
  apolloClient,
  to,
  _,
  next
) {
  if (to.matched.some((record) => record.meta.requiresSubmissionAccess)) {
    let access = false
    const submissionId = to.params.id
    const user = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS
      })
      .then(({ data: { currentUser } }) => currentUser)

    const submission = user.submissions.filter((submission) => {
      return submission.id == submissionId
    })

    if (submission.length) {
      const s = submission[0]

      // Redirect when the submission is a Draft
      if (s.status === "DRAFT") {
        next({ name: "submission:draft", params: { id: s.id } })
        return false
      }

      // Anyone assigned to the submission holds `view`.
      access = !!s.abilities?.view
    }

    // Not in the viewer's submissions list: not directly assigned, so fall back
    // to the submission's own `view` ability. The server grants it to
    // publication admins, editors and the application administrator on the
    // submissions they manage.
    if (!access && !submission.length) {
      const fetched = await fetchSubmission(apolloClient, submissionId)
      access = !!fetched?.abilities?.view
    }

    if (!access) {
      next({ name: "error403" })
    } else {
      next()
    }
  } else {
    next()
  }
}

export async function beforeEachRequiresDraftAccess(apolloClient, to, _, next) {
  if (to.matched.some((record) => record.meta.requiresDraftAccess)) {
    let access = false
    const submissionId = to.params.id
    const user = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS,
        fetchPolicy: "network-only"
      })
      .then(({ data: { currentUser } }) => currentUser)

    const submission = user.submissions.filter((submission) => {
      return submission.id == submissionId
    })

    if (submission.length) {
      const s = submission[0]

      // The draft is the author's editing surface — only submitters reach it.
      if (["submitter"].some((role) => role === s.my_role)) {
        access = true
      }
    }
    if (!access) {
      next({ name: "error403" })
    } else {
      next()
    }
  } else {
    next()
  }
}

export async function beforeEachRequiresPreviewAccess(
  apolloClient,
  to,
  _,
  next
) {
  if (to.matched.some((record) => record.meta.requiresPreviewAccess)) {
    let access = false
    const submissionId = to.params.id
    const user = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS
      })
      .then(({ data: { currentUser } }) => currentUser)

    const submission = user.submissions.filter((submission) => {
      return submission.id == submissionId
    })

    if (submission.length) {
      const s = submission[0]

      // Redirect when the submission is not a Draft
      if (s.status !== "DRAFT") {
        next({ name: "submission:view", params: { id: s.id } })
        return false
      }

      // The draft preview is for the authoring side — submitters and review
      // coordinators only. Reviewers are excluded even though they can view the
      // submission, so this stays keyed on the assignment, not the `view`
      // ability (which editors and publication admins also hold).
      if (
        ["submitter", "review_coordinator"].some((role) => role === s.my_role)
      ) {
        access = true
      }

      // Deny Reviewers
      if ("reviewer" === s.my_role) {
        access = false
      }
    }

    // Allow Application Administrators
    if (!access && user.abilities?.access_admin) {
      access = true
    }

    if (!access) {
      next({ name: "error403" })
    } else {
      next()
    }
  } else {
    next()
  }
}

export async function beforeEachRequiresViewAccess(apolloClient, to, _, next) {
  if (to.matched.some((record) => record.meta.requiresViewAccess)) {
    let access = false
    const submissionId = to.params.id
    const user = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS
      })
      .then(({ data: { currentUser } }) => currentUser)

    const submission = user.submissions.filter((submission) => {
      return submission.id == submissionId
    })

    if (submission.length) {
      const s = submission[0]

      // Redirect when the submission is a Draft
      if (s.status === "DRAFT") {
        next({ name: "submission:preview", params: { id: s.id } })
        return false
      }

      // Redirect when the submission is not Initially Submitted
      if (s.status !== "INITIALLY_SUBMITTED") {
        next({ name: "submission:review", params: { id: s.id } })
        return false
      }

      // Anyone assigned to the submission holds `view`.
      access = !!s.abilities?.view

      // Deny Reviewers when the submission is in a nonreviewable state — the
      // `view` ability is unconditional for them, so this stays keyed on the
      // assignment and status.
      const nonreviewableStates = new Set([
        "REJECTED",
        "RESUBMISSION_REQUESTED"
      ])
      if ("reviewer" === s.my_role && nonreviewableStates.has(s.status)) {
        access = false
      }
    }

    // Not in the viewer's submissions list: not directly assigned, so fall back
    // to the submission's own `view` ability (held by publication admins,
    // editors and the application administrator). This runs only for unassigned
    // viewers, so it cannot re-grant an assigned reviewer denied above.
    if (!access && !submission.length) {
      const fetched = await fetchSubmission(apolloClient, submissionId)
      access = !!fetched?.abilities?.view
    }

    if (!access) {
      next({ name: "error403" })
    } else {
      next()
    }
  } else {
    next()
  }
}

export async function beforeEachRequiresReviewAccess(
  apolloClient,
  to,
  _,
  next
) {
  if (to.matched.some((record) => record.meta.requiresReviewAccess)) {
    let access = false
    const submissionId = to.params.id
    const user = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS,
        fetchPolicy: "network-only"
      })
      .then(({ data: { currentUser } }) => currentUser)

    const submission = user.submissions.filter((submission) => {
      return submission.id == submissionId
    })

    if (submission.length) {
      const s = submission[0]

      // Redirect when the submission is a Draft
      if (s.status === "DRAFT") {
        next({ name: "submission:preview", params: { id: s.id } })
        return false
      }

      // Redirect when the submission is Initially Submitted
      if (s.status === "INITIALLY_SUBMITTED") {
        next({ name: "submission:view", params: { id: s.id } })
        return false
      }

      // Anyone assigned to the submission holds `view`.
      access = !!s.abilities?.view

      // Deny Reviewers when the submission is in a nonreviewable state — the
      // `view` ability is unconditional for them, so this stays keyed on the
      // assignment and status.
      const nonreviewableStates = new Set([
        "REJECTED",
        "RESUBMISSION_REQUESTED"
      ])
      if ("reviewer" === s.my_role && nonreviewableStates.has(s.status)) {
        access = false
      }
    }

    // Not in the viewer's submissions list: not directly assigned, so fall back
    // to the submission's own `view` ability (held by publication admins,
    // editors and the application administrator). This runs only for unassigned
    // viewers, so it cannot re-grant an assigned reviewer denied above.
    if (!access && !submission.length) {
      const fetched = await fetchSubmission(apolloClient, submissionId)
      access = !!fetched?.abilities?.view
    }

    if (!access) {
      next({ name: "error403" })
    } else {
      next()
    }
  } else {
    next()
  }
}

export async function beforeEachRequiresExportAccess(
  apolloClient,
  to,
  _,
  next
) {
  if (to.matched.some((record) => record.meta.requiresExportAccess)) {
    let access = false
    const submissionId = to.params.id
    const user = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS
      })
      .then(({ data: { currentUser } }) => currentUser)

    const submission = user.submissions.filter((submission) => {
      return submission.id == submissionId
    })

    // Export is gated on the scoped `export` ability, which the resolver grants
    // to submitters, review coordinators, editors, publication admins and the
    // application administrator — never plain reviewers.
    const exportableStates = new Set([
      "REJECTED",
      "RESUBMISSION_REQUESTED",
      "ACCEPTED_AS_FINAL",
      "ARCHIVED",
      "EXPIRED"
    ])

    if (submission.length) {
      const s = submission[0]

      access = !!s.abilities?.export

      // Deny when the submission is not in an exportable state
      if (!exportableStates.has(s.status)) {
        access = false
      }
    }

    // Not in the viewer's submissions list: not directly assigned, so fall back
    // to the submission's own `export` ability, which the server grants to
    // unassigned publication admins, editors and the application administrator.
    if (!access && !submission.length) {
      const fetched = await fetchSubmission(apolloClient, submissionId)
      access = !!fetched?.abilities?.export
    }

    if (!access) {
      next({ name: "error403" })
    } else {
      next()
    }
  } else {
    next()
  }
}

export async function beforeEachRequiresAppAdmin(apolloClient, to, _, next) {
  if (to.matched.some((record) => record.meta.requiresAppAdmin)) {
    const access = await apolloClient
      .query({
        query: CURRENT_USER
      })
      .then(({ data: { currentUser } }) => !!currentUser?.abilities?.access_admin)

    if (!access) {
      next({ name: "error403" })
    } else {
      next()
    }
  } else {
    next()
  }
}

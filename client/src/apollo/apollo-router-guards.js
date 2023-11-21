import { SessionStorage } from "quasar"
import { checkRole } from "src/use/user"
import {
  CURRENT_USER,
  GET_SUBMISSION,
  CURRENT_USER_SUBMISSIONS,
} from "src/graphql/queries"

const { isEditor, isPublicationAdmin } = checkRole()

async function isPubAdminOrEditor(apolloClient, submissionId) {
  const submission = await apolloClient
    .query({
      query: GET_SUBMISSION,
      variables: { id: submissionId },
    })
    .then(({ data: { submission } }) => submission)

  await submission
  if (
    isPublicationAdmin(submission.publication) ||
    isEditor(submission.publication)
  ) {
    return true
  }
  return false
}

export async function beforeEachRequiresAuth(apolloClient, to, _, next) {
  if (to.matched.some((record) => record.meta.requiresAuth)) {
    const user = await apolloClient
      .query({
        query: CURRENT_USER,
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
  next,
) {
  if (to.matched.some((record) => record.meta.requiresSubmissionAccess)) {
    let access = false
    const submissionId = to.params.id
    const user = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS,
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

      // Allow those who are assigned to the submission
      if (
        ["review_coordinator", "reviewer", "submitter"].some(
          (role) => role === s.my_role,
        )
      ) {
        access = true
      }
    }

    // Allow Publication Administrators and Editors
    if (!access) {
      try {
        access = await isPubAdminOrEditor(apolloClient, submissionId).then(
          (result) => result,
        )
      } catch (error) {
        access = false
      }
    }

    // Allow Application Administrators
    if (!access && user.roles.length > 0) {
      if (
        user.roles.some((role) => role.name === "Application Administrator")
      ) {
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

export async function beforeEachRequiresDraftAccess(apolloClient, to, _, next) {
  if (to.matched.some((record) => record.meta.requiresDraftAccess)) {
    let access = false
    const submissionId = to.params.id
    const user = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS,
        fetchPolicy: "network-only",
      })
      .then(({ data: { currentUser } }) => currentUser)

    const submission = user.submissions.filter((submission) => {
      return submission.id == submissionId
    })

    if (submission.length) {
      const s = submission[0]

      // Only allow submitters access
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
  next,
) {
  if (to.matched.some((record) => record.meta.requiresPreviewAccess)) {
    let access = false
    const submissionId = to.params.id
    const user = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS,
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

      // Allow Submitters and Review Coordinators
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
    if (!access && user.roles.length > 0) {
      if (
        user.roles.some((role) => role.name === "Application Administrator")
      ) {
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

export async function beforeEachRequiresViewAccess(apolloClient, to, _, next) {
  if (to.matched.some((record) => record.meta.requiresViewAccess)) {
    let access = false
    const submissionId = to.params.id
    const user = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS,
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

      // Allow those who are assigned to the submission
      if (
        ["review_coordinator", "reviewer", "submitter"].some(
          (role) => role === s.my_role,
        )
      ) {
        access = true
      }

      // Deny Reviewers when the submission is in a nonreviewable state
      const nonreviewableStates = new Set([
        "REJECTED",
        "RESUBMISSION_REQUESTED",
      ])
      if ("reviewer" === s.my_role && nonreviewableStates.has(s.status)) {
        access = false
      }
    }

    // Allow Publication Administrators and Editors
    if (!access) {
      try {
        access = await isPubAdminOrEditor(apolloClient, submissionId).then(
          (result) => result,
        )
      } catch (error) {
        access = false
      }
    }

    // Allow Application Administrators
    if (!access && user.roles.length > 0) {
      if (
        user.roles.some((role) => role.name === "Application Administrator")
      ) {
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

export async function beforeEachRequiresReviewAccess(
  apolloClient,
  to,
  _,
  next,
) {
  if (to.matched.some((record) => record.meta.requiresReviewAccess)) {
    let access = false
    const submissionId = to.params.id
    const user = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS,
        fetchPolicy: "network-only",
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

      // Allow those who are assigned to the submission
      if (
        ["review_coordinator", "reviewer", "submitter"].some(
          (role) => role === s.my_role,
        )
      ) {
        access = true
      }

      // Deny Reviewers when the submission is in a nonreviewable state
      const nonreviewableStates = new Set([
        "REJECTED",
        "RESUBMISSION_REQUESTED",
      ])
      if ("reviewer" === s.my_role && nonreviewableStates.has(s.status)) {
        access = false
      }
    }

    // Allow Publication Administrators and Editors
    if (!access) {
      try {
        access = await isPubAdminOrEditor(apolloClient, submissionId).then(
          (result) => result,
        )
      } catch (error) {
        access = false
      }
    }

    // Allow Application Administrators
    if (!access && user.roles.length > 0) {
      if (
        user.roles.some((role) => role.name === "Application Administrator")
      ) {
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

export async function beforeEachRequiresExportAccess(
  apolloClient,
  to,
  _,
  next,
) {
  if (to.matched.some((record) => record.meta.requiresExportAccess)) {
    let access = false
    const submissionId = to.params.id
    const user = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS,
      })
      .then(({ data: { currentUser } }) => currentUser)

    const submission = user.submissions.filter((submission) => {
      return submission.id == submissionId
    })

    if (submission.length) {
      const s = submission[0]

      // Allow those who are assigned to the submission
      if (
        ["review_coordinator", "submitter"].some((role) => role === s.my_role)
      ) {
        access = true
      }

      // Deny Reviewers
      if ("reviewer" === s.my_role) {
        access = false
      }

      // Deny when the submission is not in an exportable state
      const exportableStates = new Set([
        "REJECTED",
        "RESUBMISSION_REQUESTED",
        "ACCEPTED_AS_FINAL",
        "ARCHIVED",
        "EXPIRED",
      ])
      if (!exportableStates.has(s.status)) {
        access = false
      }
    }

    // Allow Publication Administrators and Editors
    if (!access) {
      try {
        access = await isPubAdminOrEditor(apolloClient, submissionId).then(
          (result) => result,
        )
      } catch (error) {
        access = false
      }
    }

    // Allow Application Administrators
    if (!access && user.roles.length > 0) {
      if (
        user.roles.some((role) => role.name === "Application Administrator")
      ) {
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

export async function beforeEachRequiresAppAdmin(apolloClient, to, _, next) {
  if (to.matched.some((record) => record.meta.requiresAppAdmin)) {
    let access = false
    const highest_privileged_role = await apolloClient
      .query({
        query: CURRENT_USER,
      })
      .then(({ data: { currentUser } }) => currentUser.highest_privileged_role)

    if (highest_privileged_role == "application_admin") {
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

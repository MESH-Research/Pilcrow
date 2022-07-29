import { SessionStorage } from "quasar"
import { CURRENT_USER, CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"

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
  next
) {
  if (to.matched.some((record) => record.meta.requiresSubmissionAccess)) {
    const submissionId = to.params.id
    const submissions = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS,
      })
      .then(
        ({
          data: {
            currentUser: { submissions },
          },
        }) => submissions.filter((submission) => submission.id == submissionId)
      )
    if (submissions.length === 0) {
      to.meta.requiresRoles = [
        "Editor",
        "Publication Administrator",
        "Application Administrator",
      ]
      beforeEachRequiresRoles(apolloClient, to, _, next)
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
      })
      .then(({ data: { currentUser } }) => currentUser)

    const submission = user.submissions.filter((submission) => {
      return submission.id == submissionId
    })

    if (submission.length) {
      const s = submission[0]

      // Allow Review Coordinators, Reviewers, and Submitters
      if (
        ["review_coordinator", "reviewer", "submitter"].some(
          (role) => role === s.my_role
        )
      ) {
        access = true
      }

      // Deny Reviewers when the submission is in a nonreviewable state
      const nonreviewableStates = new Set(["DRAFT", "INITIALLY_SUBMITTED"])
      if ("reviewer" === s.my_role && nonreviewableStates.has(s.status)) {
        access = false
      }

      // Allow Publication Administrators and Editors
      if (
        ["publication_admin", "editor"].some(
          (role) => role === s.publication.my_role
        )
      ) {
        access = true
      }
    }

    // Allow Application Administrators
    if (user.roles.length > 0) {
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

export async function beforeEachRequiresRoles(apolloClient, to, _, next) {
  if (to.matched.some((record) => record.meta.requiresRoles)) {
    const requiredRoles = to.matched
      .filter((record) => record.meta.requiresRoles)
      .map((record) => record.meta.requiresRoles)
      .flat(2)
    const roles = await apolloClient
      .query({
        query: CURRENT_USER,
      })
      .then(
        ({
          data: {
            currentUser: { roles },
          },
        }) => roles.map((r) => r.name)
      )
    if (!roles.some((role) => requiredRoles.includes(role))) {
      next({ name: "error403" })
    } else {
      next()
    }
  } else {
    next()
  }
}

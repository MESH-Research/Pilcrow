import { SessionStorage } from "quasar"
import gql from "graphql-tag"
import { CURRENT_USER, CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"

declare module "vue-router" {
  interface RouteMeta {
    /**
     * Auto-route access gate. Names an entry in the gate registry below (e.g.
     * "adminArea"); the single {@link beforeEachGate} hook resolves and runs it.
     * Preferred over the legacy `requires*` meta booleans for file-based routes
     * declared with `definePage`.
     */
    gate?: GateName
  }
}

/**
 * A route gate: given the apollo client and the target route, decide whether
 * navigation is allowed. Resolves to `true` to allow, or to a route location to
 * redirect to (an error page, or a more appropriate screen).
 *
 * @callback Gate
 */

/**
 * The gate registry — the single source of truth for declarative route access.
 * Auto-routes name a gate via `meta.gate`; legacy routes reach the same gates
 * through {@link LEGACY_META_GATES}. Adding a capability-driven area is a matter
 * of adding a gate here, not wiring a new `beforeEach` hook.
 */
const GATES = {
  /**
   * The /admin area is open to anyone holding ANY global `admin_*` ability — the
   * union of admin capabilities, not a single "is admin" flag. A new global role
   * that adds an `admin_*` ability widens admin access with no change here.
   */
  adminArea: async (apolloClient) => {
    const user = await apolloClient
      .query({ query: CURRENT_USER })
      .then(({ data: { currentUser } }) => currentUser)

    return user?.abilities?.admin_area === true ? true : { name: "error403" }
  }
}

type GateName = keyof typeof GATES

/**
 * Legacy bridge. Manual routes (src/router/routes.ts) still declare access with
 * boolean meta flags; map each to its gate so the one gate runner covers them
 * until they migrate to the auto-route `meta: { gate }` form. New routes should
 * declare `meta.gate` directly rather than add entries here.
 */
const LEGACY_META_GATES = {
  requiresAppAdmin: "adminArea"
}

/**
 * Resolve the gate a route requires, preferring the auto-route `meta.gate`
 * declaration and falling back to the legacy boolean meta flags. Returns null
 * when the route declares no gate.
 *
 * @param {import("vue-router").RouteLocationNormalized} to
 * @returns {GateName | null}
 */
function resolveGateName(to) {
  for (const record of to.matched) {
    if (typeof record.meta.gate === "string") {
      return record.meta.gate
    }
    for (const legacyKey of Object.keys(LEGACY_META_GATES)) {
      if (record.meta[legacyKey]) {
        return LEGACY_META_GATES[legacyKey]
      }
    }
  }

  return null
}

/**
 * The single declarative access guard. Resolves the route's gate (auto-route or
 * legacy) and runs it, allowing navigation or redirecting per the gate's
 * verdict. Routes with no gate pass straight through.
 */
export async function beforeEachGate(apolloClient, to, _, next) {
  const gateName = resolveGateName(to)
  if (!gateName) {
    next()
    return
  }

  const gate = GATES[gateName]
  if (!gate) {
    next()
    return
  }

  const result = await gate(apolloClient, to)
  next(result === true ? undefined : result)
}

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
      status
      abilities {
        view
        update_title
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

    // Not in the viewer's submissions list: unassigned viewers who still hold
    // the submission's own `view` ability reach the draft preview — the
    // application administrator (via the server super-admin short-circuit) and
    // the publication's admins/editors. Keyed on the submission flag, never a
    // global "is admin" surrogate.
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

    // Export has no server mutation — it renders already-viewable content — so
    // this is a client policy, not a server-enforced ability. Eligible parties
    // are the authoring/coordinating roles: exactly those who may edit the
    // submission (`update_title`) — submitter, review coordinator, editor,
    // publication admin, application administrator — never a plain reviewer. The
    // exportable-state restriction is layered on top.
    const exportableStates = new Set([
      "REJECTED",
      "RESUBMISSION_REQUESTED",
      "ACCEPTED_AS_FINAL",
      "ARCHIVED",
      "EXPIRED"
    ])

    if (submission.length) {
      const s = submission[0]

      access = !!s.abilities?.update_title && exportableStates.has(s.status)
    }

    // Not in the viewer's submissions list: not directly assigned, so fall back
    // to the submission's own `update_title` ability, held by unassigned
    // publication admins, editors and the application administrator — still only
    // in an exportable state.
    if (!access && !submission.length) {
      const fetched = await fetchSubmission(apolloClient, submissionId)
      access =
        !!fetched?.abilities?.update_title &&
        exportableStates.has(fetched?.status)
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

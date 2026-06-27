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

/**
 * Submission states from which a viewer may export — export renders content
 * already visible, so the gate is `view` plus this state restriction.
 */
const EXPORTABLE_STATES = new Set([
  "REJECTED",
  "RESUBMISSION_REQUESTED",
  "ACCEPTED_AS_FINAL",
  "ARCHIVED",
  "EXPIRED"
])

/**
 * States in which a Reviewer loses access even though their `view` ability is
 * unconditional — the denial stays keyed on the assignment and status.
 */
const NONREVIEWABLE_STATES = new Set(["REJECTED", "RESUBMISSION_REQUESTED"])

/**
 * Build a `beforeEach` guard for a submission-scoped route. Every such guard
 * shares one skeleton — gate on a `meta` flag, load the viewer's submissions,
 * find the target, then decide — so the per-guard logic collapses to two small
 * functions:
 *
 * @param {object}   config
 * @param {string}   config.metaKey   The `meta` boolean that arms this guard.
 * @param {string}  [config.fetchPolicy] Apollo fetch policy for the submissions
 *   query (e.g. "network-only" where stale assignment data would misroute).
 * @param {(s: object) => boolean | object} config.decideAssigned  Given the
 *   viewer's own copy of the submission, return `true`/`false` for access, or a
 *   route location to redirect to (e.g. a status-appropriate screen).
 * @param {(fetched: object | null) => boolean} [config.decideUnassigned]  The
 *   fallback for viewers not directly assigned (so the submission never appears
 *   in their list) who may still reach it through a publication role — the
 *   server grants admins, editors and the application administrator the scoped
 *   `view` ability on the submission itself. Omit to deny unassigned viewers.
 */
function submissionAccessGuard({
  metaKey,
  fetchPolicy = undefined,
  decideAssigned,
  decideUnassigned = undefined
}) {
  return async function (apolloClient, to, _, next) {
    if (!to.matched.some((record) => record.meta[metaKey])) {
      next()
      return
    }

    const submissionId = to.params.id
    const user = await apolloClient
      .query({
        query: CURRENT_USER_SUBMISSIONS,
        ...(fetchPolicy ? { fetchPolicy } : {})
      })
      .then(({ data: { currentUser } }) => currentUser)

    const submission = user.submissions.filter((submission) => {
      return submission.id == submissionId
    })

    let access = false
    if (submission.length) {
      const verdict = decideAssigned(submission[0])
      // A route location means "redirect"; a boolean is the access decision.
      if (typeof verdict !== "boolean") {
        next(verdict)
        return
      }
      access = verdict
    } else if (decideUnassigned) {
      const fetched = await fetchSubmission(apolloClient, submissionId)
      access = decideUnassigned(fetched)
    }

    next(access ? undefined : { name: "error403" })
  }
}

export const beforeEachRequiresSubmissionAccess = submissionAccessGuard({
  metaKey: "requiresSubmissionAccess",
  decideAssigned: (s) => {
    // Redirect when the submission is a Draft.
    if (s.status === "DRAFT") {
      return { name: "submission:draft", params: { id: s.id } }
    }
    // Anyone assigned to the submission holds `view`.
    return !!s.abilities?.view
  },
  decideUnassigned: (fetched) => !!fetched?.abilities?.view
})

export const beforeEachRequiresDraftAccess = submissionAccessGuard({
  metaKey: "requiresDraftAccess",
  fetchPolicy: "network-only",
  // The draft is the author's editing surface — only submitters reach it, and
  // there is no unassigned fallback.
  decideAssigned: (s) => ["submitter"].some((role) => role === s.my_role)
})

export const beforeEachRequiresPreviewAccess = submissionAccessGuard({
  metaKey: "requiresPreviewAccess",
  decideAssigned: (s) => {
    // Redirect when the submission is not a Draft.
    if (s.status !== "DRAFT") {
      return { name: "submission:view", params: { id: s.id } }
    }
    // The draft preview is for the authoring side — submitters and review
    // coordinators only. Reviewers are excluded even though they can view the
    // submission, so this stays keyed on the assignment, not the `view` ability
    // (which editors and publication admins also hold).
    let access = ["submitter", "review_coordinator"].some(
      (role) => role === s.my_role
    )
    // Deny Reviewers.
    if ("reviewer" === s.my_role) {
      access = false
    }
    return access
  },
  // Unassigned viewers who still hold the submission's own `view` ability reach
  // the draft preview — the application administrator (via the server
  // super-admin short-circuit) and the publication's admins/editors.
  decideUnassigned: (fetched) => !!fetched?.abilities?.view
})

export const beforeEachRequiresViewAccess = submissionAccessGuard({
  metaKey: "requiresViewAccess",
  decideAssigned: (s) => {
    // Redirect when the submission is a Draft.
    if (s.status === "DRAFT") {
      return { name: "submission:preview", params: { id: s.id } }
    }
    // Redirect when the submission is not Initially Submitted.
    if (s.status !== "INITIALLY_SUBMITTED") {
      return { name: "submission:review", params: { id: s.id } }
    }
    // Anyone assigned to the submission holds `view`.
    let access = !!s.abilities?.view
    if ("reviewer" === s.my_role && NONREVIEWABLE_STATES.has(s.status)) {
      access = false
    }
    return access
  },
  decideUnassigned: (fetched) => !!fetched?.abilities?.view
})

export const beforeEachRequiresReviewAccess = submissionAccessGuard({
  metaKey: "requiresReviewAccess",
  fetchPolicy: "network-only",
  decideAssigned: (s) => {
    // Redirect when the submission is a Draft.
    if (s.status === "DRAFT") {
      return { name: "submission:preview", params: { id: s.id } }
    }
    // Redirect when the submission is Initially Submitted.
    if (s.status === "INITIALLY_SUBMITTED") {
      return { name: "submission:view", params: { id: s.id } }
    }
    // Anyone assigned to the submission holds `view`.
    let access = !!s.abilities?.view
    // Deny Reviewers when the submission is in a nonreviewable state — the
    // `view` ability is unconditional for them, so this stays keyed on the
    // assignment and status.
    if ("reviewer" === s.my_role && NONREVIEWABLE_STATES.has(s.status)) {
      access = false
    }
    return access
  },
  decideUnassigned: (fetched) => !!fetched?.abilities?.view
})

export const beforeEachRequiresExportAccess = submissionAccessGuard({
  metaKey: "requiresExportAccess",
  // Export has no server mutation — it renders content the viewer can already
  // see — so the gate is exactly "can you view this submission" (`view`), not a
  // proxy on an edit ability whose constraints may drift independently. The
  // exportable-state restriction is layered on top.
  decideAssigned: (s) => !!s.abilities?.view && EXPORTABLE_STATES.has(s.status),
  decideUnassigned: (fetched) =>
    !!fetched?.abilities?.view && EXPORTABLE_STATES.has(fetched?.status)
})

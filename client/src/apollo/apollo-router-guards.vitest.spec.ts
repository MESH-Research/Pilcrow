import { afterEach, describe, expect, it, vi } from "vitest"
import { CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"
import {
  beforeEachRequiresAuth,
  beforeEachGate,
  beforeEachRequiresSubmissionAccess,
  beforeEachRequiresDraftAccess,
  beforeEachRequiresPreviewAccess,
  beforeEachRequiresViewAccess,
  beforeEachRequiresReviewAccess,
  beforeEachRequiresExportAccess
} from "./apollo-router-guards"

const apolloMock = {
  query: vi.fn()
}

/**
 * Drive the submissions query and the unassigned-fallback fetch from one place.
 * `submissions` is the viewer's own list; `fetched` is what the colocated
 * GuardSubmissionAccess query returns for the unassigned fallback (null = no
 * access). Anything that isn't the CURRENT_USER_SUBMISSIONS query is the
 * fallback fetch.
 */
function mockApollo({ submissions = [], fetched = null } = {}) {
  apolloMock.query.mockImplementation((arg) => {
    if (arg.query === CURRENT_USER_SUBMISSIONS) {
      return Promise.resolve({ data: { currentUser: { submissions } } })
    }
    return Promise.resolve({ data: { submission: fetched } })
  })
}

/** Run a submission guard against route `meta` and return the spied `next`. */
async function runGuard(guard, meta) {
  const to = { matched: [{ meta }], params: { id: "1" } }
  const next = vi.fn()
  await guard(apolloMock, to, undefined, next)
  return next
}

/** Stub the current-user query (the adminArea gate's only query). */
function mockCurrentUser(currentUser) {
  apolloMock.query.mockResolvedValue({ data: { currentUser } })
}

/** Stub the colocated publication query (the publicationSetup gate's query). */
function mockPublication(publication) {
  apolloMock.query.mockResolvedValue({ data: { publication } })
}

/** Run the declarative gate runner against route `meta`, returning `next`. */
async function runGate(meta, params = {}) {
  const to = { matched: [{ meta }], params }
  const next = vi.fn()
  await beforeEachGate(apolloMock, to, undefined, next)
  return next
}

describe("requiresAuth router hook", () => {
  afterEach(() => {
    apolloMock.query.mockClear()
  })

  it("allows navigation when user is logged in", async () => {
    const to = {
      matched: [{ meta: { requiresAuth: true } }]
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: {
          id: 1
        }
      }
    })

    const next = vi.fn()

    await beforeEachRequiresAuth(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("redirects to login page when user is not logged in", async () => {
    const setItem = vi.spyOn(window.sessionStorage, "setItem")

    const to = {
      matched: [{ meta: { requiresAuth: true } }]
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: null
      }
    })

    const next = vi.fn()

    await beforeEachRequiresAuth(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBe("/login")

    expect(window.sessionStorage.setItem).toHaveBeenCalled()
    setItem.mockReset()
  })

  it("allows navigation when route is missing the requiresAuth meta property", async () => {
    const to = {
      matched: [{ meta: {} }]
    }

    const next = vi.fn()

    await beforeEachRequiresAuth(apolloMock, to, undefined, next)

    expect(apolloMock.query).not.toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })
})

describe("adminArea gate", () => {
  afterEach(() => {
    apolloMock.query.mockClear()
  })

  it("allows navigation when the viewer holds an admin_* ability (auto-route meta.gate)", async () => {
    mockCurrentUser({ id: 1, abilities: { admin_area: true } })

    const next = await runGate({ gate: "adminArea" })

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("allows navigation via the legacy requiresAppAdmin meta flag", async () => {
    mockCurrentUser({ id: 1, abilities: { admin_area: true } })

    const next = await runGate({ requiresAppAdmin: true })

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("redirects to error403 when the viewer holds no admin_* ability", async () => {
    mockCurrentUser({ id: 1, abilities: { admin_area: false } })

    const next = await runGate({ gate: "adminArea" })

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toStrictEqual({ name: "error403" })
  })

  it("allows navigation when the route declares no gate", async () => {
    const next = await runGate({})

    expect(apolloMock.query).not.toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })
})

describe("publicationSetup gate", () => {
  afterEach(() => {
    apolloMock.query.mockReset()
  })

  it("allows navigation when the viewer holds the publication's `update` ability", async () => {
    mockPublication({ id: "1", abilities: { update: true } })

    const next = await runGate({ gate: "publicationSetup" }, { id: "1" })

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("redirects to error403 when the viewer lacks `update`", async () => {
    mockPublication({ id: "1", abilities: { update: false } })

    const next = await runGate({ gate: "publicationSetup" }, { id: "1" })

    expect(next.mock.calls[0][0]).toStrictEqual({ name: "error403" })
  })

  it("redirects to error403 when the publication cannot be fetched", async () => {
    // A rejected query (no view access) resolves the fetch to null.
    apolloMock.query.mockRejectedValue(new Error("forbidden"))

    const next = await runGate({ gate: "publicationSetup" }, { id: "1" })

    expect(next.mock.calls[0][0]).toStrictEqual({ name: "error403" })
  })
})

describe("requiresSubmissionAccess guard", () => {
  afterEach(() => apolloMock.query.mockReset())

  it("passes through when the route lacks the meta flag", async () => {
    const next = await runGuard(beforeEachRequiresSubmissionAccess, {})
    expect(apolloMock.query).not.toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("redirects a Draft to the draft editor", async () => {
    mockApollo({ submissions: [{ id: "1", status: "DRAFT" }] })
    const next = await runGuard(beforeEachRequiresSubmissionAccess, {
      requiresSubmissionAccess: true
    })
    expect(next.mock.calls[0][0]).toStrictEqual({
      name: "submission:draft",
      params: { id: "1" }
    })
  })

  it("allows an assigned viewer holding `view`", async () => {
    mockApollo({
      submissions: [
        { id: "1", status: "ACCEPTED_AS_FINAL", abilities: { view: true } }
      ]
    })
    const next = await runGuard(beforeEachRequiresSubmissionAccess, {
      requiresSubmissionAccess: true
    })
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("denies an assigned viewer without `view`", async () => {
    mockApollo({
      submissions: [
        { id: "1", status: "ACCEPTED_AS_FINAL", abilities: { view: false } }
      ]
    })
    const next = await runGuard(beforeEachRequiresSubmissionAccess, {
      requiresSubmissionAccess: true
    })
    expect(next.mock.calls[0][0]).toStrictEqual({ name: "error403" })
  })

  it("falls back to the submission's own `view` for an unassigned viewer", async () => {
    mockApollo({
      submissions: [],
      fetched: { id: "1", abilities: { view: true } }
    })
    const next = await runGuard(beforeEachRequiresSubmissionAccess, {
      requiresSubmissionAccess: true
    })
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("denies an unassigned viewer the submission cannot be fetched for", async () => {
    mockApollo({ submissions: [], fetched: null })
    const next = await runGuard(beforeEachRequiresSubmissionAccess, {
      requiresSubmissionAccess: true
    })
    expect(next.mock.calls[0][0]).toStrictEqual({ name: "error403" })
  })
})

describe("requiresDraftAccess guard", () => {
  afterEach(() => apolloMock.query.mockReset())

  it("allows an assigned submitter", async () => {
    mockApollo({ submissions: [{ id: "1", my_role: "submitter" }] })
    const next = await runGuard(beforeEachRequiresDraftAccess, {
      requiresDraftAccess: true
    })
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("denies an assigned non-submitter", async () => {
    mockApollo({ submissions: [{ id: "1", my_role: "reviewer" }] })
    const next = await runGuard(beforeEachRequiresDraftAccess, {
      requiresDraftAccess: true
    })
    expect(next.mock.calls[0][0]).toStrictEqual({ name: "error403" })
  })

  it("denies an unassigned viewer without fetching the submission", async () => {
    mockApollo({ submissions: [] })
    const next = await runGuard(beforeEachRequiresDraftAccess, {
      requiresDraftAccess: true
    })
    // Only the submissions query runs — the draft has no unassigned fallback.
    expect(apolloMock.query).toHaveBeenCalledTimes(1)
    expect(next.mock.calls[0][0]).toStrictEqual({ name: "error403" })
  })
})

describe("requiresPreviewAccess guard", () => {
  afterEach(() => apolloMock.query.mockReset())

  it("redirects a non-Draft to the view screen", async () => {
    mockApollo({ submissions: [{ id: "1", status: "INITIALLY_SUBMITTED" }] })
    const next = await runGuard(beforeEachRequiresPreviewAccess, {
      requiresPreviewAccess: true
    })
    expect(next.mock.calls[0][0]).toStrictEqual({
      name: "submission:view",
      params: { id: "1" }
    })
  })

  it("allows an assigned submitter on a Draft", async () => {
    mockApollo({
      submissions: [{ id: "1", status: "DRAFT", my_role: "submitter" }]
    })
    const next = await runGuard(beforeEachRequiresPreviewAccess, {
      requiresPreviewAccess: true
    })
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("denies a Reviewer on a Draft", async () => {
    mockApollo({
      submissions: [{ id: "1", status: "DRAFT", my_role: "reviewer" }]
    })
    const next = await runGuard(beforeEachRequiresPreviewAccess, {
      requiresPreviewAccess: true
    })
    expect(next.mock.calls[0][0]).toStrictEqual({ name: "error403" })
  })

  it("falls back to `view` for an unassigned viewer", async () => {
    mockApollo({
      submissions: [],
      fetched: { id: "1", abilities: { view: true } }
    })
    const next = await runGuard(beforeEachRequiresPreviewAccess, {
      requiresPreviewAccess: true
    })
    expect(next.mock.calls[0][0]).toBeUndefined()
  })
})

describe("requiresViewAccess guard", () => {
  afterEach(() => apolloMock.query.mockReset())

  it("redirects a Draft to the preview screen", async () => {
    mockApollo({ submissions: [{ id: "1", status: "DRAFT" }] })
    const next = await runGuard(beforeEachRequiresViewAccess, {
      requiresViewAccess: true
    })
    expect(next.mock.calls[0][0]).toStrictEqual({
      name: "submission:preview",
      params: { id: "1" }
    })
  })

  it("redirects a non-Initially-Submitted submission to the review screen", async () => {
    mockApollo({ submissions: [{ id: "1", status: "ACCEPTED_AS_FINAL" }] })
    const next = await runGuard(beforeEachRequiresViewAccess, {
      requiresViewAccess: true
    })
    expect(next.mock.calls[0][0]).toStrictEqual({
      name: "submission:review",
      params: { id: "1" }
    })
  })

  it("allows an assigned viewer on an Initially Submitted submission", async () => {
    mockApollo({
      submissions: [
        { id: "1", status: "INITIALLY_SUBMITTED", abilities: { view: true } }
      ]
    })
    const next = await runGuard(beforeEachRequiresViewAccess, {
      requiresViewAccess: true
    })
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("falls back to `view` for an unassigned viewer", async () => {
    mockApollo({
      submissions: [],
      fetched: { id: "1", abilities: { view: true } }
    })
    const next = await runGuard(beforeEachRequiresViewAccess, {
      requiresViewAccess: true
    })
    expect(next.mock.calls[0][0]).toBeUndefined()
  })
})

describe("requiresReviewAccess guard", () => {
  afterEach(() => apolloMock.query.mockReset())

  it("redirects a Draft to the preview screen", async () => {
    mockApollo({ submissions: [{ id: "1", status: "DRAFT" }] })
    const next = await runGuard(beforeEachRequiresReviewAccess, {
      requiresReviewAccess: true
    })
    expect(next.mock.calls[0][0]).toStrictEqual({
      name: "submission:preview",
      params: { id: "1" }
    })
  })

  it("redirects an Initially Submitted submission to the view screen", async () => {
    mockApollo({ submissions: [{ id: "1", status: "INITIALLY_SUBMITTED" }] })
    const next = await runGuard(beforeEachRequiresReviewAccess, {
      requiresReviewAccess: true
    })
    expect(next.mock.calls[0][0]).toStrictEqual({
      name: "submission:view",
      params: { id: "1" }
    })
  })

  it("allows an assigned non-reviewer on a reviewable submission", async () => {
    mockApollo({
      submissions: [
        {
          id: "1",
          status: "UNDER_REVIEW",
          my_role: "editor",
          abilities: { view: true }
        }
      ]
    })
    const next = await runGuard(beforeEachRequiresReviewAccess, {
      requiresReviewAccess: true
    })
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("denies a Reviewer when the submission is in a nonreviewable state", async () => {
    mockApollo({
      submissions: [
        {
          id: "1",
          status: "REJECTED",
          my_role: "reviewer",
          abilities: { view: true }
        }
      ]
    })
    const next = await runGuard(beforeEachRequiresReviewAccess, {
      requiresReviewAccess: true
    })
    expect(next.mock.calls[0][0]).toStrictEqual({ name: "error403" })
  })

  it("falls back to `view` for an unassigned viewer", async () => {
    mockApollo({
      submissions: [],
      fetched: { id: "1", abilities: { view: true } }
    })
    const next = await runGuard(beforeEachRequiresReviewAccess, {
      requiresReviewAccess: true
    })
    expect(next.mock.calls[0][0]).toBeUndefined()
  })
})

describe("requiresExportAccess guard", () => {
  afterEach(() => apolloMock.query.mockReset())

  it("allows an assigned viewer in an exportable state", async () => {
    mockApollo({
      submissions: [{ id: "1", status: "REJECTED", abilities: { view: true } }]
    })
    const next = await runGuard(beforeEachRequiresExportAccess, {
      requiresExportAccess: true
    })
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("denies an assigned viewer in a non-exportable state", async () => {
    mockApollo({
      submissions: [
        { id: "1", status: "INITIALLY_SUBMITTED", abilities: { view: true } }
      ]
    })
    const next = await runGuard(beforeEachRequiresExportAccess, {
      requiresExportAccess: true
    })
    expect(next.mock.calls[0][0]).toStrictEqual({ name: "error403" })
  })

  it("falls back to `view` plus an exportable state for an unassigned viewer", async () => {
    mockApollo({
      submissions: [],
      fetched: { id: "1", status: "ARCHIVED", abilities: { view: true } }
    })
    const next = await runGuard(beforeEachRequiresExportAccess, {
      requiresExportAccess: true
    })
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("denies an unassigned viewer in a non-exportable state", async () => {
    mockApollo({
      submissions: [],
      fetched: {
        id: "1",
        status: "INITIALLY_SUBMITTED",
        abilities: { view: true }
      }
    })
    const next = await runGuard(beforeEachRequiresExportAccess, {
      requiresExportAccess: true
    })
    expect(next.mock.calls[0][0]).toStrictEqual({ name: "error403" })
  })
})

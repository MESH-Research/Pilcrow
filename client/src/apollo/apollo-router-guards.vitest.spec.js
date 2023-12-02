import { afterEach, describe, expect, it, vi } from 'vitest'
import { beforeEachRequiresAuth, beforeEachRequiresAppAdmin } from './apollo-router-guards'

const apolloMock = {
  query: vi.fn(),
}

describe("requiresAuth router hook", () => {
  afterEach(() => {
    apolloMock.query.mockClear()
  })

  it("allows navigation when user is logged in", async () => {
    const to = {
      matched: [{ meta: { requiresAuth: true } }],
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: {
          id: 1,
        },
      },
    })

    const next = vi.fn()

    await beforeEachRequiresAuth(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("redirects to login page when user is not logged in", async () => {
    const setItem = vi.spyOn(window.sessionStorage, 'setItem')

    const to = {
      matched: [{ meta: { requiresAuth: true } }],
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: null,
      },
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
      matched: [{ meta: {} }],
    }

    const next = vi.fn()

    await beforeEachRequiresAuth(apolloMock, to, undefined, next)

    expect(apolloMock.query).not.toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })
})

describe("requiresAppAdmin router hook", () => {
  afterEach(() => {
    apolloMock.query.mockClear()
  })

  it("allows navigation when user is an Application Administrator", async () => {
    const to = {
      matched: [{ meta: { requiresAppAdmin: true } }],
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: {
          id: 1,
          highest_privileged_role: "application_admin",
        },
      },
    })

    const next = vi.fn()

    await beforeEachRequiresAppAdmin(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("redirects to error403 when user is not an Application Administrator", async () => {
    const to = {
      matched: [{ meta: { requiresAppAdmin: true } }],
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: {
          id: 1,
          highest_privileged_role: null
        },
      },
    })

    const next = vi.fn()

    await beforeEachRequiresAppAdmin(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toStrictEqual({ name: "error403" })
  })

  it("allows navigation when requiresAppAdmin meta property is not present", async () => {
    const to = {
      matched: [{ meta: {} }],
    }

    const next = vi.fn()

    await beforeEachRequiresAppAdmin(apolloMock, to, undefined, next)

    expect(apolloMock.query).not.toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

})

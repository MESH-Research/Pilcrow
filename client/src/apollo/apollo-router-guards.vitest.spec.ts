import { afterEach, describe, expect, it, vi } from "vitest"
import { beforeEachRequiresAuth, beforeEachGate } from "./apollo-router-guards"

const apolloMock = {
  query: vi.fn()
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
    const to = {
      matched: [{ meta: { gate: "adminArea" } }]
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: {
          id: 1,
          abilities: { admin_area: true }
        }
      }
    })

    const next = vi.fn()

    await beforeEachGate(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("allows navigation via the legacy requiresAppAdmin meta flag", async () => {
    const to = {
      matched: [{ meta: { requiresAppAdmin: true } }]
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: {
          id: 1,
          abilities: { admin_area: true }
        }
      }
    })

    const next = vi.fn()

    await beforeEachGate(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })

  it("redirects to error403 when the viewer holds no admin_* ability", async () => {
    const to = {
      matched: [{ meta: { gate: "adminArea" } }]
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: {
          id: 1,
          abilities: { admin_area: false }
        }
      }
    })

    const next = vi.fn()

    await beforeEachGate(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toStrictEqual({ name: "error403" })
  })

  it("allows navigation when the route declares no gate", async () => {
    const to = {
      matched: [{ meta: {} }]
    }

    const next = vi.fn()

    await beforeEachGate(apolloMock, to, undefined, next)

    expect(apolloMock.query).not.toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBeUndefined()
  })
})

import {
  beforeEachRequiresAuth,
  beforeEachRequiresRoles,
} from "./apollo-router-guards"
import * as Q from "quasar"

jest.mock("quasar", () => ({
  SessionStorage: {
    set: jest.fn(),
  },
}))
const setSession = Q.SessionStorage.set

const apolloMock = {
  query: jest.fn(),
}

describe("requiresAuth router hook", () => {
  afterEach(() => {
    apolloMock.query.mockClear()
    Q.SessionStorage.set.mockClear()
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

    const next = jest.fn()

    await beforeEachRequiresAuth(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBe(undefined)
  })

  it("redirects to login page when user is not logged in", async () => {
    const to = {
      matched: [{ meta: { requiresAuth: true } }],
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: null,
      },
    })

    const next = jest.fn()

    await beforeEachRequiresAuth(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(setSession).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).not.toBe(undefined)
  })

  it("allows navigation when route is missing the requiresAuth meta property", async () => {
    const to = {
      matched: [{ meta: {} }],
    }

    const next = jest.fn()

    await beforeEachRequiresAuth(apolloMock, to, undefined, next)

    expect(apolloMock.query).not.toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBe(undefined)
  })
})

describe("requiresRoles router hook", () => {
  afterEach(() => {
    apolloMock.query.mockClear()
  })

  it("allows navigation when user has required role", async () => {
    const to = {
      matched: [{ meta: { requiresRoles: ["Application Admin"] } }],
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: {
          id: 1,
          roles: [{ name: "Application Admin" }],
        },
      },
    })

    const next = jest.fn()

    await beforeEachRequiresRoles(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBe(undefined)
  })

  it("redirects to error403 when user does not have required role", async () => {
    const to = {
      matched: [{ meta: { requiresRoles: ["Application Administrator"] } }],
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: {
          id: 1,
          roles: [],
        },
      },
    })

    const next = jest.fn()

    await beforeEachRequiresRoles(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).not.toBe(undefined)
  })

  it("allows navigation when requiresRoles meta property is not present", async () => {
    const to = {
      matched: [{ meta: {} }],
    }

    const next = jest.fn()

    await beforeEachRequiresRoles(apolloMock, to, undefined, next)

    expect(apolloMock.query).not.toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBe(undefined)
  })

  it("allows navigation when user has one of the required roles", async () => {
    const to = {
      matched: [
        {
          meta: {
            requiresRoles: [
              "Application Administrator",
              "Publication Administrator",
              "Editor",
            ],
          },
        },
      ],
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: {
          id: 1,
          roles: [{ name: "Application Administrator" }],
        },
      },
    })
    const next = jest.fn()
    await beforeEachRequiresRoles(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBe(undefined)
  })

  it("redirects to error403 page when user does not have any required roles", async () => {
    const to = {
      matched: [
        {
          meta: {
            requiresRoles: ["Application Administrator", "testExtraRole"],
          },
        },
      ],
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: {
          id: 1,
          roles: [{ name: "Submitter" }],
        },
      },
    })
    const next = jest.fn()
    await beforeEachRequiresRoles(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).not.toBe(undefined)
  })

  it("allows navigation if user has all required roles", async () => {
    const to = {
      matched: [
        {
          meta: {
            requiresRoles: ["Application Administrator", "testExtraRole"],
          },
        },
      ],
    }

    apolloMock.query.mockResolvedValue({
      data: {
        currentUser: {
          id: 1,
          roles: [
            { name: "Application Administrator" },
            { name: "testExtraRole" },
          ],
        },
      },
    })
    const next = jest.fn()
    await beforeEachRequiresRoles(apolloMock, to, undefined, next)

    expect(apolloMock.query).toHaveBeenCalled()
    expect(next).toHaveBeenCalled()
    expect(next.mock.calls[0][0]).toBe(undefined)
  })
})

import { InMemoryCache } from "@apollo/client/core"
import {
  installQuasarPlugin,
} from "@quasar/quasar-app-extension-testing-unit-vitest"
import { ApolloClients } from "@vue/apollo-composable"
import { mount } from "@vue/test-utils"
import flushPromises from "flush-promises"
import { createMockClient } from "test/vitest/apolloClient"
import { CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"
import { useCurrentUser } from "src/use/user"
import { describe, expect, it, test, vi } from 'vitest'
import { ref } from "vue"
import DashboardPage from "./DashboardPage.vue"
vi.mock("src/use/user", () => ({
  useCurrentUser: vi.fn(),
}))

installQuasarPlugin()
describe("Dashboard Page", () => {
  const cache = new InMemoryCache({
    addTypename: true,
  })
  const mockClient = createMockClient({
    defaultOptions: { watchQuery: { fetchPolicy: "network-only" } },
    cache,
  })
  const wrapperFactory = async () => {
    const wrapper = mount(DashboardPage, {
      global: {
        provide: {
          [ApolloClients]: { default: mockClient },
        },
        stubs: ["router-link", "i18n-t"],
        mocks: {
          $t: (t) => t,
        },
      },
    })
    await flushPromises()
    return wrapper
  }

  const CurrentUserSubmissions = vi.fn()
  mockClient.setRequestHandler(CURRENT_USER_SUBMISSIONS, CurrentUserSubmissions)

  function mockSubmission(id, status, role) {
    return {
      id: id,
      title: "Pilcrow Test Submission for Jest",
      status: status,
      my_role: role,
      effective_role: role,
      publication: {
        id: "1",
        name: "Pilcrow Test Publication 1",
        my_role: null,
      },
    }
  }

  function getData() {
    return {
      data: {
        currentUser: {
          id: "5",
          roles: [],
          submissions: [],
        },
      },
    }
  }

  it("mounts without errors", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })
    const mockData = getData()
    mockData.data.currentUser.submissions.push(
      mockSubmission("100", "UNDER_REVIEW", "submitter")
    )
    CurrentUserSubmissions.mockResolvedValue(mockData)
    const wrapper = await wrapperFactory()
    expect(wrapper).toBeTruthy()
  })

  test("the reviews table appears for a user with reviews", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })

    const mockData = getData()
    mockData.data.currentUser.submissions.push(
      mockSubmission("100", "UNDER_REVIEW", "reviewer"),
      mockSubmission("101", "UNDER_REVIEW", "reviewer"),
      mockSubmission("102", "REJECTED", "reviewer")
    )
    CurrentUserSubmissions.mockResolvedValue(mockData)
    const wrapper = await wrapperFactory()
    expect(wrapper.findAll('[data-cy="reviews_table"]').length).toBe(1)
    expect(wrapper.findAll('[data-cy="coordinations_table"]').length).toBe(0)
    expect(wrapper.findAll('[data-cy="submissions_table"]').length).toBe(0)
    expect(wrapper.findAllComponents({ name: "submission-table" }).length).toBe(
      1
    )
  })

  test("the reviews table does not appear for a user with no reviews", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })

    const mockData = getData()
    CurrentUserSubmissions.mockResolvedValue(mockData)
    const wrapper = await wrapperFactory()
    expect(wrapper.findAllComponents({ name: "submission-table" }).length).toBe(
      0
    )
  })
})

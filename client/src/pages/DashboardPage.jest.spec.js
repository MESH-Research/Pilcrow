import { mount } from "@vue/test-utils"
import {
  installQuasarPlugin,
  qLayoutInjections,
} from "@quasar/quasar-app-extension-testing-unit-jest"
import DashboardPage from "./DashboardPage.vue"
import { ApolloClients } from "@vue/apollo-composable"
import { createMockClient } from "mock-apollo-client"
import { useCurrentUser } from "src/use/user"
import { ref } from "vue"
import { CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"
import flushPromises from "flush-promises"
import { InMemoryCache } from "@apollo/client/core"

jest.mock("src/use/user", () => ({
  useCurrentUser: jest.fn(),
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
          ...qLayoutInjections(),
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

  const CurrentUserSubmissions = jest.fn()
  mockClient.setRequestHandler(CURRENT_USER_SUBMISSIONS, CurrentUserSubmissions)

  function getSubmissionData() {
    return {
      data: {
        currentUser: {
          id: "5",
          roles: [],
          submissions: [
            {
              id: "100",
              title: "Pilcrow Test Submission 1",
              status: "UNDER_REVIEW",
              my_role: "reviewer",
              effective_role: "reviewer",
              publication: {
                id: "1",
                name: "Pilcrow Test Publication 1",
                my_role: null,
              },
            },
            {
              id: "101",
              title: "Pilcrow Test Submission 2",
              status: "INITIALLY_SUBMITTED",
              my_role: "reviewer",
              effective_role: "reviewer",
              publication: {
                id: "1",
                name: "Pilcrow Test Publication 1",
                my_role: null,
              },
            },
            {
              id: "102",
              title: "Pilcrow Test Submission 3",
              status: "REJECTED",
              my_role: "reviewer",
              effective_role: "reviewer",
              publication: {
                id: "1",
                name: "Pilcrow Test Publication 1",
                my_role: null,
              },
            },
            {
              id: "103",
              title: "Pilcrow Test Submission 4",
              status: "RESUBMISSION_REQUESTED",
              my_role: "reviewer",
              effective_role: "reviewer",
              publication: {
                id: "1",
                name: "Pilcrow Test Publication 1",
                my_role: null,
              },
            },
            {
              id: "104",
              title: "Pilcrow Test Submission 5",
              status: "AWAITING_REVIEW",
              my_role: "reviewer",
              effective_role: "reviewer",
              publication: {
                id: "1",
                name: "Pilcrow Test Publication 1",
                my_role: null,
              },
            },
          ],
        },
      },
    }
  }

  it("mounts without errors", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })
    CurrentUserSubmissions.mockResolvedValue(getSubmissionData())
    const wrapper = await wrapperFactory()
    expect(wrapper).toBeTruthy()
  })
})

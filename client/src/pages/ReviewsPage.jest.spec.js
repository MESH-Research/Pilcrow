import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import ReviewsPage from "./ReviewsPage.vue"
import { ApolloClients } from "@vue/apollo-composable"
import { createMockClient } from "mock-apollo-client"
import { CURRENT_USER_SUBMISSIONS } from "src/graphql/queries"
import flushPromises from "flush-promises"

jest.mock("quasar", () => ({
  ...jest.requireActual("quasar"),
  useQuasar: () => ({
    notify: jest.fn(),
  }),
}))
jest.mock("vue-i18n", () => ({
  useI18n: () => ({
    t: (t) => t,
  }),
}))

installQuasarPlugin()
describe("Reviews Page", () => {
  const mockClient = createMockClient()
  const makeWrapper = async () => {
    const wrapper = mount(ReviewsPage, {
      global: {
        provide: {
          [ApolloClients]: { default: mockClient },
        },
        stubs: ["router-link"],
        mocks: {
          $t: (token) => token,
        },
      },
    })
    await flushPromises()
    return wrapper
  }

  const CurrentUserSubmissions = jest.fn()
  mockClient.setRequestHandler(CURRENT_USER_SUBMISSIONS, CurrentUserSubmissions)

  test("all submission tables appear for a user as a review coordinator", async () => {
    CurrentUserSubmissions.mockResolvedValue({
      data: {
        currentUser: {
          id: 1000,
          roles: {
            name: "Application Administrator",
          },
          submissions: [
            {
              id: "1",
              title: "Jest Submission 1",
              status: "INITIALLY_SUBMITTED",
              my_role: "submitter",
              effective_role: "submitter",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
              },
            },
            {
              id: "2",
              title: "Jest Submission 2",
              status: "RESUBMISSION_REQUESTED",
              my_role: "reviewer",
              effective_role: "reviewer",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
              },
            },
            {
              id: "3",
              title: "Jest Submission 3",
              status: "AWAITING_REVIEW",
              my_role: "review_coordinator",
              effective_role: "review_coordinator",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
              },
            },
            {
              id: "4",
              title: "Jest Submission 4",
              status: "REJECTED",
              my_role: "review_coordinator",
              effective_role: "review_coordinator",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
              },
            },
            {
              id: "5",
              title: "Jest Submission 5",
              status: "INITIALLY_SUBMITTED",
              my_role: "reviewer",
              effective_role: "reviewer",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
              },
            },
          ],
        },
      },
    })

    const wrapper = await makeWrapper()
    expect(
      wrapper.findAllComponents({ name: "submission-table" })
    ).toHaveLength(2)
    CurrentUserSubmissions.mockClear()
  })

  test("only one submission table appears for a user as a reviewer", async () => {
    CurrentUserSubmissions.mockResolvedValue({
      data: {
        currentUser: {
          id: 1000,
          roles: {
            name: "Application Administrator",
          },
          submissions: [
            {
              id: "1",
              title: "Jest Submission 1",
              status: "INITIALLY_SUBMITTED",
              my_role: "submitter",
              effective_role: "submitter",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
              },
            },
            {
              id: "2",
              title: "Jest Submission 2",
              status: "RESUBMISSION_REQUESTED",
              my_role: "reviewer",
              effective_role: "reviewer",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
              },
            },
          ],
        },
      },
    })
    const wrapper = await makeWrapper()
    expect(
      wrapper.findAllComponents({ name: "submission-table" })
    ).toHaveLength(1)
  })
})

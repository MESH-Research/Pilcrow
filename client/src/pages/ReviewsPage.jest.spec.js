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

  test("all reviews appear within the list", async () => {
    CurrentUserSubmissions.mockResolvedValue({
      data: {
        submissions: {
          paginatorInfo: {
            __typename: "PaginatorInfo",
            count: 5,
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
          },
          data: [
            {
              id: "1",
              title: "Jest Submission 1",
              status: "INITIALLY_SUBMITTED",
              my_role: "submitter",
              publication: { name: "Jest Publication" },
              files: [],
            },
            {
              id: "2",
              title: "Jest Submission 2",
              status: "RESUBMISSION_REQUESTED",
              my_role: "reviewer",
              publication: { name: "Jest Publication" },
              files: [],
            },
            {
              id: "3",
              title: "Jest Submission 3",
              status: "AWAITING_REVIEW",
              my_role: "review_coordinator",
              effective_role: "review_coordinator",
              publication: { name: "Jest Publication" },
              files: [],
            },
            {
              id: "4",
              title: "Jest Submission 4",
              status: "REJECTED",
              my_role: "",
              effective_role: "review_coordinator",
              publication: { name: "Jest Publication" },
              files: [],
            },
            {
              id: "5",
              title: "Jest Submission 5",
              status: "INITIALLY_SUBMITTED",
              my_role: "",
              effective_role: "",
              publication: { name: "Jest Publication" },
              files: [],
            },
          ],
        },
      },
    })
    const wrapper = await makeWrapper()
    expect(wrapper.findAllComponents({ name: "q-item" })).toHaveLength(5)
  })
})

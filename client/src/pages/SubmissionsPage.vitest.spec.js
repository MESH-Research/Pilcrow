import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { ApolloClients } from "@vue/apollo-composable"
import { mount, flushPromises } from "@vue/test-utils"
import { createMockClient } from "test/vitest/apolloClient"
import { GET_PUBLICATIONS, GET_SUBMISSIONS } from "src/graphql/queries"
import { describe, expect, test, vi } from "vitest"
import SubmissionsPage from "./SubmissionsPage.vue"

vi.mock("quasar", () => ({
  ...vi.requireActual("quasar"),
  useQuasar: () => ({
    notify: vi.fn(),
  }),
}))

installQuasarPlugin()
describe("submissions page mount", () => {
  const mockClient = createMockClient()
  const makeWrapper = async () => {
    const wrapper = mount(SubmissionsPage, {
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

  const getSubsHandler = vi.fn()
  mockClient.setRequestHandler(GET_SUBMISSIONS, getSubsHandler)
  const getPubsHandler = vi.fn()
  mockClient.setRequestHandler(GET_PUBLICATIONS, getPubsHandler)

  const mockPublications = () => {
    getPubsHandler.mockResolvedValue({
      data: {
        publications: {
          paginatorInfo: {
            __typename: "PaginatorInfo",
            count: 1,
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
          },
          data: [{ id: 1, name: "Jest Publication", home_page_content: "" }],
        },
      },
    })
  }

  test("all existing submissions appear within the list", async () => {
    getSubsHandler.mockResolvedValue({
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
              effective_role: "submitter",
              publication: { name: "Jest Publication" },
              files: [],
            },
            {
              id: "2",
              title: "Jest Submission 2",
              status: "RESUBMISSION_REQUESTED",
              my_role: "reviewer",
              effective_role: "reviewer",
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
    mockPublications()
    const wrapper = await makeWrapper()
    expect(wrapper.findAllComponents({ name: "q-item" })).toHaveLength(5)
  })

  //TODO: Test submission creation
})

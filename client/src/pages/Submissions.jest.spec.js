import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import SubmissionsPage from "./SubmissionPage.vue"
import { ApolloClients } from "@vue/apollo-composable"
import { createMockClient } from "mock-apollo-client"
import { GET_PUBLICATIONS, GET_SUBMISSIONS } from "src/graphql/queries"
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

  const getSubsHandler = jest.fn()
  mockClient.setRequestHandler(GET_SUBMISSIONS, getSubsHandler)
  const getPubsHandler = jest.fn()
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
          data: [{ id: 1, name: "Jest Publication" }],
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
              publication: { name: "Jest Publication" },
              files: [],
            },
            {
              id: "2",
              title: "Jest Submission 2",
              publication: { name: "Jest Publication" },
              files: [],
            },
            {
              id: "3",
              title: "Jest Submission 3",
              publication: { name: "Jest Publication" },
              files: [],
            },
            {
              id: "4",
              title: "Jest Submission 4",
              publication: { name: "Jest Publication" },
              files: [],
            },
            {
              id: "5",
              title: "Jest Submission 5",
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

import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import PublicationIndexPage from "./PublicationIndexPage.vue"
import { ApolloClients } from "@vue/apollo-composable"
import { createMockClient } from "mock-apollo-client"
import { GET_PUBLICATIONS } from "src/graphql/queries"
import flushPromises from "flush-promises"

jest.mock("quasar", () => ({
  ...jest.requireActual("quasar"),
  useQuasar: () => ({
    notify: jest.fn(),
  }),
}))

jest.mock("vue-router", () => ({
  useRouter: () => ({
    push: jest.fn(),
  }),
}))

jest.mock("vue-i18n", () => ({
  useI18n: () => ({
    t: (t) => t,
  }),
}))

installQuasarPlugin()
describe("publications page mount", () => {
  const mockClient = createMockClient()
  const makeWrapper = () =>
    mount(PublicationIndexPage, {
      global: {
        provide: {
          [ApolloClients]: { default: mockClient },
        },
        mocks: {
          $t: (t) => t,
        },
      },
    })

  beforeEach(async () => {
    jest.resetAllMocks()
  })

  it("mounts without errors", () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
  })
  const getPubHandler = jest.fn()
  mockClient.setRequestHandler(GET_PUBLICATIONS, getPubHandler)

  test("all existing publications appear within the list", async () => {
    getPubHandler.mockResolvedValue({
      data: {
        publications: {
          data: [
            {
              id: "1",
              name: "Sample Jest Publication 1",
              home_page_content: "",
            },
            {
              id: "2",
              name: "Sample Jest Publication 2",
              home_page_content: "",
            },
            {
              id: "3",
              name: "Sample Jest Publication 3",
              home_page_content: "",
            },
            {
              id: "4",
              name: "Sample Jest Publication 4",
              home_page_content: "",
            },
          ],
          paginatorInfo: {
            __typename: "PaginatorInfo",
            count: 4,
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
          },
        },
      },
    })
    const wrapper = makeWrapper()
    await flushPromises()

    expect(getPubHandler).toHaveBeenCalled()
    //+1 to account for the create publication header
    expect(wrapper.findAll(".q-item")).toHaveLength(5)
  })

  //TODO: Test for no publications returned
})

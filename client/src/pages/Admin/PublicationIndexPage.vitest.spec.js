import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { ApolloClients } from "@vue/apollo-composable"
import { mount, flushPromises } from "@vue/test-utils"
import { createMockClient } from "test/vitest/apolloClient"
import { GET_PUBLICATIONS } from "src/graphql/queries"
import PublicationIndexPage from "./PublicationIndexPage.vue"
import { Notify } from 'quasar'
import { beforeEach, describe, expect, it, test, vi } from "vitest"

vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: vi.fn(),
  }),
}))

installQuasarPlugin({ plugins: { Notify } })
describe("publications page mount", () => {
  const mockClient = createMockClient()
  const makeWrapper = () =>
    mount(PublicationIndexPage, {
      global: {
        provide: {
          [ApolloClients]: { default: mockClient },
        },
      },
    })

  beforeEach(async () => {
    vi.resetAllMocks()
  })

  it("mounts without errors", () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
  })
  const getPubHandler = vi.fn()
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

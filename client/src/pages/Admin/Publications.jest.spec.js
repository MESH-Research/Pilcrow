import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import PublicationsPage from "./PublicationsPage.vue"
import { ApolloClients } from "@vue/apollo-composable"
import { createMockClient } from "mock-apollo-client"
import { CREATE_PUBLICATION } from "src/graphql/mutations"
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
    t: jest.fn().mockImplementation((t) => t),
  }),
}))

installQuasarPlugin()
describe("publications page mount", () => {
  const mockClient = createMockClient()
  const makeWrapper = () =>
    mount(PublicationsPage, {
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
  const mutationHandler = jest.fn()
  mockClient.setRequestHandler(CREATE_PUBLICATION, mutationHandler)

  test("all existing publications appear within the list", async () => {
    getPubHandler.mockResolvedValue({
      data: {
        publications: {
          data: [
            { id: "1", name: "Sample Jest Publication 1" },
            { id: "2", name: "Sample Jest Publication 2" },
            { id: "3", name: "Sample Jest Publication 3" },
            { id: "4", name: "Sample Jest Publication 4" },
          ],
          paginatorInfo: {
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
    expect(wrapper.findAllComponents({ name: "q-item" })).toHaveLength(4)
  })

  test("publications can be created", async () => {
    const name = "New Jest Publication Name"
    mutationHandler.mockResolvedValue({
      data: {
        createPublication: {
          id: 1,
          name,
        },
      },
    })
    const wrapper = makeWrapper()
    wrapper.findComponent({ ref: "nameInput" }).setValue(name)
    wrapper.findComponent({ ref: "submitBtn" }).trigger("submit")
    await flushPromises()

    expect(mutationHandler).toBeCalledWith(expect.objectContaining({ name }))
    expect(wrapper.vm.notify).toBeCalledWith(
      expect.objectContaining({ color: "positive" })
    )
  })

  //TODO: Test for no publications returned
})

import ContentPage from "./ContentPage.vue"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { ApolloClients } from "@vue/apollo-composable"
import { createMockClient } from "mock-apollo-client"
import { ref as mockRef } from "vue"
import { UPDATE_PUBLICATION_CONTENT } from "src/graphql/mutations"
import flushPromises from "flush-promises"

jest.mock("src/use/forms", () => ({
  ...jest.requireActual("src/use/forms"),
  useDirtyGuard: () => {},
  useFormState: () => ({
    dirty: mockRef(false),
    saved: mockRef(false),
    state: mockRef("idle"),
    queryLoading: mockRef(false),
    mutationLoading: mockRef(false),
    errorMessage: mockRef(""),
  }),
}))

installQuasarPlugin()
describe("BasicPage", () => {
  const mockClient = createMockClient()
  const makeWrapper = async () => {
    const wrapper = mount(ContentPage, {
      global: {
        provide: {
          [ApolloClients]: { default: mockClient },
        },
        mocks: {
          $t: (t) => t,
        },
        stubs: ["update-content-form"],
      },
      props: {
        publication: {
          id: "1",
          home_page_content: "Test Content",
          new_submission_content: "Test Content",
        },
      },
    })
    await flushPromises()
    return wrapper
  }

  const mutateHandler = jest.fn()
  mockClient.setRequestHandler(UPDATE_PUBLICATION_CONTENT, mutateHandler)

  beforeEach(() => {
    jest.resetAllMocks()
  })

  test("able to mount", async () => {
    const wrapper = await makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  test("saves publication data", async () => {
    const newData = {
      home_page_content: "New Content",
      new_submission_content: "Test Content",
    }

    mutateHandler.mockResolvedValue({
      data: { updatePublication: { id: "1", ...newData } },
    })

    const wrapper = await makeWrapper()
    await wrapper
      .findComponent({ ref: "form" })
      .vm.$emit("save", { field: "home_page_content", content: "New Content" })

    expect(mutateHandler).toHaveBeenCalledWith({
      id: "1",
      home_page_content: "New Content",
    })
  })

  test("sets error on failure", async () => {
    mutateHandler.mockRejectedValue({})

    const wrapper = await makeWrapper()
    await wrapper.findComponent({ ref: "form" }).vm.$emit("save", {})
    await flushPromises()

    expect(wrapper.vm.formState.errorMessage.value).not.toBe("")
  })
})

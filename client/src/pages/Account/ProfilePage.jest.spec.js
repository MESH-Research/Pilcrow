import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import ProfilePage from "./ProfilePage.vue"
import { createMockClient } from "mock-apollo-client"
import { ApolloClients } from "@vue/apollo-composable"
import flushPromises from "flush-promises"
import { UPDATE_USER } from "src/graphql/mutations"
import { CURRENT_USER } from "src/graphql/queries"
import { ref as mockRef } from "vue"
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

jest.mock("quasar", () => ({
  ...jest.requireActual("quasar"),
  useQuasar: () => ({
    notify: jest.fn(),
  }),
}))

installQuasarPlugin()
describe("Profile", () => {
  const mockClient = createMockClient()
  const makeWrapper = async () => {
    const wrapper = mount(ProfilePage, {
      global: {
        provide: {
          [ApolloClients]: { default: mockClient },
        },
        mocks: {
          $t: (t) => t,
          stubs: ["router-link"],
        },
      },
    })
    await flushPromises()
    return wrapper
  }

  beforeEach(() => {
    jest.resetAllMocks()
    requestHandler.mockResolvedValue({
      data: {
        currentUser: {
          id: 1,
          username: "test",
          name: "TestDoe",
          email: "test@example.com",
          email_verified_at: null,
          roles: [],
        },
      },
    })
  })

  const mutateHandler = jest.fn()
  const requestHandler = jest.fn()
  mockClient.setRequestHandler(UPDATE_USER, mutateHandler)
  mockClient.setRequestHandler(CURRENT_USER, requestHandler)

  const accountData = () => ({
    id: 1,
    username: "username1",
    name: "Test User",
    email: "testemail@example.com",
  })

  beforeEach(() => {
    jest.resetAllMocks()
  })

  it("mounts without errors", async () => {
    const wrapper = await makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  test("saves account data", async () => {
    const initialData = accountData()
    const newData = accountData()
    newData.username = "Tester User"

    requestHandler.mockResolvedValue({ data: { currentUser: initialData } })
    mutateHandler.mockResolvedValue({
      data: { updateUser: { ...newData, updated_at: "soonish" } },
    })
    const wrapper = await makeWrapper()
    await wrapper.findComponent({ ref: "form" }).vm.$emit("save", newData)

    expect(requestHandler).toHaveBeenCalledTimes(1)
    expect(mutateHandler).toBeCalledWith({
      ...newData,
    })
  })

  test("sets error on failure", async () => {
    const formData = accountData()
    requestHandler.mockResolvedValue({ data: { currentUser: formData } })
    mutateHandler.mockRejectedValue({})

    const wrapper = await makeWrapper()

    await wrapper.findComponent({ ref: "form" }).vm.$emit("save", formData)
    await flushPromises()

    expect(wrapper.vm.formState.errorMessage.value).not.toBe("")
  })
})

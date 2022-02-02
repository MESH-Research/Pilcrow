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
  it("mounts without errors", async () => {
    const wrapper = await makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  test("form submits valid data", async () => {
    const wrapper = await makeWrapper()
    const userData = {
      name: "Joe Doe",
      username: "joedoe",
      password: "albancub4Grac&",
      email: "joedoe@example.com",
    }

    mutateHandler.mockResolvedValue({
      data: {
        updateUser: {
          ...userData,
          id: 1,
          updated_at: "nowish",
        },
      },
    })

    await wrapper.findComponent({ ref: "nameInput" }).setValue(userData.name)
    await wrapper
      .findComponent({ ref: "usernameInput" })
      .setValue(userData.username)
    await wrapper
      .findComponent({ ref: "passwordInput" })
      .setValue(userData.password)
    await wrapper.findComponent({ ref: "emailInput" }).setValue(userData.email)

    await wrapper.findComponent({ name: "q-form" }).trigger("submit")
    await flushPromises()
    expect(mutateHandler).toBeCalledWith(
      expect.objectContaining({ ...userData, id: 1 })
    )
    expect(wrapper.vm.notify).toBeCalledWith(
      expect.objectContaining({ color: "positive" })
    )
  })
  //TODO: Test failing validation cases.
})

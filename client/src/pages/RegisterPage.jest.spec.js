import RegisterPage from "./RegisterPage.vue"
import { mount } from "@vue/test-utils"
import {
  installQuasarPlugin,
  qLayoutInjections,
} from "@quasar/quasar-app-extension-testing-unit-jest"
import { CREATE_USER, LOGIN } from "src/graphql/mutations"
import { createMockClient } from "mock-apollo-client"
import { ApolloClients } from "@vue/apollo-composable"
import flushPromises from "flush-promises"
import { omit } from "lodash"

jest.mock("quasar", () => ({
  ...jest.requireActual("quasar"),
  SessionStorage: {
    remove: () => {},
    getItem: () => null,
  },
}))

jest.mock("vue-router", () => ({
  useRouter: () => ({
    push: jest.fn(),
  }),
}))

installQuasarPlugin()
describe("RegisterPage", () => {
  const wrapperFactory = (mocks = []) => {
    const mockClient = createMockClient()

    mocks?.forEach((mock) => {
      mockClient.setRequestHandler(...mock)
    })

    return {
      wrapper: mount(RegisterPage, {
        global: {
          provide: {
            ...qLayoutInjections(),
            [ApolloClients]: { default: mockClient },
          },
          stubs: ["router-link", "i18n-t"],
          mocks: {
            $t: (token) => token,
          },
        },
      }),
      mockClient,
    }
  }

  it("mounts without errors", () => {
    expect(wrapperFactory().wrapper).toBeTruthy()
  })

  test("form submits on valid data", async () => {
    const { wrapper, mockClient } = wrapperFactory()

    const user = {
      username: "user",
      password: "albancub4Grac&",
      name: "Joe Doe",
      email: "test@example.com",
      created_at: "nowish",
    }

    const mutateHandler = jest
      .fn()
      .mockResolvedValue({ data: { createUser: { id: 1, ...user } } })

    mockClient.setRequestHandler(CREATE_USER, mutateHandler)
    mockClient.setRequestHandler(
      LOGIN,
      jest.fn().mockResolvedValue({
        data: { login: { id: 1, ...user } },
      })
    )

    await wrapper.findComponent({ ref: "nameInput" }).setValue(user.name)
    await wrapper.findComponent({ ref: "emailInput" }).setValue(user.email)
    await wrapper
      .findComponent({ ref: "usernameInput" })
      .setValue(user.username)

    await wrapper
      .findComponent({ ref: "passwordInput" })
      .setValue(user.password)

    wrapper.findComponent({ name: "q-btn" }).trigger("submit")
    await flushPromises()

    expect(wrapper.vm.formErrorMsg).toBeFalsy()
    expect(mutateHandler).toBeCalledWith(
      expect.objectContaining(omit(user, "created_at"))
    )
  })
})

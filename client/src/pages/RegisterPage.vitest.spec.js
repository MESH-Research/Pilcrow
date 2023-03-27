import {
  installQuasarPlugin
} from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { omit } from "lodash"
import { installApolloClient } from "test/vitest/utils"
import { CREATE_USER, LOGIN } from "src/graphql/mutations"
import { describe, expect, it, vi } from 'vitest'
import RegisterPage from "./RegisterPage.vue"

vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: vi.fn(),
  }),
}))

installQuasarPlugin()
const mockClient = installApolloClient()

describe("RegisterPage", () => {
  const wrapperFactory = () => mount(RegisterPage, {
    global: {
      stubs: ["router-link", "i18n-t"],
    },
  })

  const createUserHandler = vi.fn()
  mockClient.setRequestHandler(CREATE_USER, createUserHandler)

  const loginHandler = vi.fn()
  mockClient.setRequestHandler(LOGIN, loginHandler)


  it("mounts without errors", () => {
    expect(wrapperFactory()).toBeTruthy()
  })

  it("submits form on valid data", async () => {
    const wrapper = wrapperFactory()

    const user = {
      username: "user",
      password: "albancub4Grac&",
      name: "Joe Doe",
      email: "test@example.com",
      created_at: "nowish",
    }

    createUserHandler.mockResolvedValue({ data: { createUser: { id: 1, ...user } } })
    loginHandler.mockResolvedValue({
      data: { login: { id: 1, ...user } },
    })


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
    expect(createUserHandler).toHaveBeenCalledWith(
      expect.objectContaining(omit(user, "created_at"))
    )
  })
})

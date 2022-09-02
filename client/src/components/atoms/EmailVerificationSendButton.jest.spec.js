import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { ApolloClients } from "@vue/apollo-composable"
import { mount } from "@vue/test-utils"
import flushPromises from "flush-promises"
import { createMockClient } from "mock-apollo-client"
import { SEND_VERIFY_EMAIL } from "src/graphql/mutations"
import EmailVerificationSendButton from "./EmailVerificationSendButton.vue"

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
describe("EmailVerificationSendButton", () => {
  const mockClient = createMockClient()
  const wrapper = mount(EmailVerificationSendButton, {
    global: {
      provide: {
        [ApolloClients]: { default: mockClient },
      },
      mocks: {
        $t: (t) => t,
      },
    },
  })

  const emailMutationHandler = jest.fn()
  mockClient.setRequestHandler(SEND_VERIFY_EMAIL, emailMutationHandler)

  beforeEach(async () => {
    jest.resetAllMocks()
  })

  it("mounts without errors", () => {
    expect(wrapper).toBeTruthy()
  })

  it("changes state on success", async () => {
    emailMutationHandler.mockResolvedValue({
      data: { sendEmailVerification: { email: "test@example.com" } },
    })

    expect(wrapper.text()).toMatch(/resend_button$/)

    await wrapper.trigger("click")
    await flushPromises()
    expect(wrapper.text()).toMatch(/resend_button_success$/)
    expect(wrapper.vm.notify).toHaveBeenCalledWith(
      expect.objectContaining({ color: "positive" })
    )

    expect(emailMutationHandler).toHaveBeenCalled()
  })

  it("returns to state on failure", async () => {
    emailMutationHandler.mockRejectedValue({})
    wrapper.vm.status = null
    await flushPromises()
    expect(wrapper.text()).toMatch(/resend_button$/)

    await wrapper.trigger("click")
    await flushPromises()

    expect(emailMutationHandler).toHaveBeenCalled()
    expect(wrapper.text()).toMatch(/resend_button$/)
    expect(wrapper.vm.notify).toHaveBeenCalledWith(
      expect.objectContaining({ color: "negative" })
    )
  })
})

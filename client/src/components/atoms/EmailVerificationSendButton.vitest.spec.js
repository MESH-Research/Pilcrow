import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { ApolloClients } from "@vue/apollo-composable"
import { mount } from "@vue/test-utils"
import flushPromises from "flush-promises"
import { createMockClient } from "mock-apollo-client"
import { SEND_VERIFY_EMAIL } from "src/graphql/mutations"
import EmailVerificationSendButton from "./EmailVerificationSendButton.vue"

import { beforeEach, describe, expect, it, vi } from "vitest"

vi.mock("quasar", () => ({
  ...vi.requireActual("quasar"),
  useQuasar: () => ({
    notify: vi.fn(),
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

  const emailMutationHandler = vi.fn()
  mockClient.setRequestHandler(SEND_VERIFY_EMAIL, emailMutationHandler)

  beforeEach(async () => {
    vi.resetAllMocks()
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

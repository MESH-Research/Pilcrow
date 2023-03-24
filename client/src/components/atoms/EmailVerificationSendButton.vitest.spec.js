import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import flushPromises from "flush-promises"
import { createMockClient } from "mock-apollo-client"
import { SEND_VERIFY_EMAIL } from "src/graphql/mutations"
import EmailVerificationSendButton from "./EmailVerificationSendButton.vue"

import { ApolloClients } from "@vue/apollo-composable"
import { Notify } from 'quasar'
import { afterEach, describe, expect, it, vi } from "vitest"

installQuasarPlugin({ plugins: { Notify } })

describe("EmailVerificationSendButton", () => {
  const mockClient = createMockClient()
  const factory = () => mount(EmailVerificationSendButton, { global: { provide: { [ApolloClients]: { default: mockClient } } } })

  const emailMutationHandler = vi.fn()
  mockClient.setRequestHandler(SEND_VERIFY_EMAIL, emailMutationHandler)

  afterEach(async () => {
    vi.resetAllMocks()
  })

  it("mounts without errors", () => {
    const wrapper = factory()
    expect(wrapper).toBeTruthy()
  })

  it("changes state on success", async () => {
    const wrapper = factory()
    emailMutationHandler.mockResolvedValue({
      data: { sendEmailVerification: { email: "test@example.com" } },
    })
    console.log(wrapper.text())
    expect(wrapper.text()).toMatch(/resend_button$/)

    await wrapper.trigger("click")
    await flushPromises()
    console.log(wrapper.text())
    expect(wrapper.text()).toMatch(/resend_button_success$/)
    expect(emailMutationHandler).toHaveBeenCalled()
  })

  it("returns to state on failure", async () => {
    const wrapper = factory()
    emailMutationHandler.mockRejectedValue({})
    wrapper.vm.status = null
    await flushPromises()
    expect(wrapper.text()).toMatch(/resend_button$/)

    await wrapper.trigger("click")
    await flushPromises()

    expect(emailMutationHandler).toHaveBeenCalled()
    expect(wrapper.text()).toMatch(/resend_button$/)
  })
})

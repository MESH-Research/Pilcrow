import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"

import { installApolloClient } from "app/test/vitest/utils"
import { SEND_VERIFY_EMAIL } from "src/graphql/mutations"
import type { SendVerificationEmailMutation } from "src/graphql/generated/graphql"
import EmailVerificationSendButton from "./EmailVerificationSendButton.vue"

import { Notify } from "quasar"
import { afterEach, describe, expect, it, vi } from "vitest"

installQuasarPlugin({ plugins: { Notify } })
const mockClient = installApolloClient()

describe("EmailVerificationSendButton", () => {
  const factory = () => mount(EmailVerificationSendButton)

  afterEach(async () => {
    vi.resetAllMocks()
  })

  it("mounts without errors", () => {
    const wrapper = factory()
    expect(wrapper).toBeTruthy()
  })

  it("changes state on success", async () => {
    const wrapper = factory()
    const handler = mockClient.getRequestHandler(SEND_VERIFY_EMAIL)
    const mockResponse: { data: SendVerificationEmailMutation } = {
      data: { sendEmailVerification: { email: "test@example.com" } }
    }
    handler.mockResolvedValue(mockResponse)
    expect(wrapper.text()).toMatch(/resend_button$/)

    await wrapper.trigger("click")
    await flushPromises()
    expect(wrapper.text()).toMatch(/resend_button_success$/)
    expect(handler).toHaveBeenCalled()
  })

  it("returns to state on failure", async () => {
    const wrapper = factory()
    const handler = mockClient.getRequestHandler(SEND_VERIFY_EMAIL)
    handler.mockRejectedValue({})
    ;(wrapper.vm as any).status = null
    await flushPromises()
    expect(wrapper.text()).toMatch(/resend_button$/)

    await wrapper.trigger("click")
    await flushPromises()

    expect(handler).toHaveBeenCalled()
    expect(wrapper.text()).toMatch(/resend_button$/)
  })
})

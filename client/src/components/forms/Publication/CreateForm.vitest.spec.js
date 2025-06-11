import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { CREATE_PUBLICATION } from "src/graphql/mutations"
import CreateForm from "./CreateForm.vue"

import { describe, expect, test, vi } from "vitest"

const mockNewStatus = vi.fn()
vi.mock("src/use/guiElements", () => ({
  useFeedbackMessages: () => ({
    newStatusMessage: mockNewStatus
  })
}))

installQuasarPlugin()
const mockClient = installApolloClient()

describe("CreateForm", () => {
  const makeWrapper = () => mount(CreateForm)

  const mutationHandler = vi.fn()
  mockClient.setRequestHandler(CREATE_PUBLICATION, mutationHandler)

  test("publications can be created", async () => {
    const warn = vi.spyOn(console, "warn").mockImplementation(() => {})
    const name = "New Jest Publication Name"
    mutationHandler.mockResolvedValue({
      data: {
        createPublication: {
          id: 1,
          name
        }
      }
    })
    const wrapper = makeWrapper()
    wrapper.trigger("create")
    wrapper.findComponent({ ref: "nameInput" }).setValue(name)
    wrapper.findComponent({ ref: "submitBtn" }).trigger("submit")
    await flushPromises()
    expect(warn).toHaveBeenCalledTimes(1)
    expect(warn).toHaveBeenCalledWith(
      expect.stringContaining(encodeURIComponent('"message":35'))
    )
    expect(warn).toHaveBeenCalledWith(
      expect.stringContaining("GetPublications")
    )
    expect(mutationHandler).toHaveBeenCalledWith(
      expect.objectContaining({ name })
    )
    expect(mockNewStatus).toHaveBeenCalledWith("success", expect.any(String))
  })
})

import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { ApolloClients } from "@vue/apollo-composable"
import { mount } from "@vue/test-utils"
import flushPromises from "flush-promises"
import { createMockClient } from "test/vitest/apolloClient"
import { CREATE_PUBLICATION } from "src/graphql/mutations"
import CreateForm from "./CreateForm.vue"

import { describe, expect, test, vi } from "vitest"

const mockNewStatus = vi.fn()
vi.mock("src/use/guiElements", () => ({
  useFeedbackMessages: () => ({
    newStatusMessage: mockNewStatus,
  }),
}))

installQuasarPlugin()
describe("CreateForm", () => {
  const mockClient = createMockClient()
  const makeWrapper = () => {
    return mount(CreateForm, {
      global: {
        mocks: {
          $t: (t) => t,
        },
        provide: {
          [ApolloClients]: { default: mockClient },
        },
      },
    })
  }

  const mutationHandler = vi.fn()
  mockClient.setRequestHandler(CREATE_PUBLICATION, mutationHandler)

  test("publications can be created", async () => {
    const warn = vi.spyOn(console, "warn").mockImplementation(() => {})
    const name = "New Jest Publication Name"
    mutationHandler.mockResolvedValue({
      data: {
        createPublication: {
          id: 1,
          name,
        },
      },
    })
    const wrapper = makeWrapper()
    wrapper.trigger("create")
    wrapper.findComponent({ ref: "nameInput" }).setValue(name)
    wrapper.findComponent({ ref: "submitBtn" }).trigger("submit")
    await flushPromises()
    expect(warn).toHaveBeenCalledTimes(1)
    expect(warn).toHaveBeenCalledWith(
      expect.stringContaining('Unknown query named "GetPublications"')
    )
    expect(mutationHandler).toHaveBeenCalledWith(
      expect.objectContaining({ name })
    )
    expect(mockNewStatus).toHaveBeenCalledWith("success", expect.any(String))
  })
})

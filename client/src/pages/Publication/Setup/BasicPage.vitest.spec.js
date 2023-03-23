import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { ApolloClients } from "@vue/apollo-composable"
import { mount } from "@vue/test-utils"
import flushPromises from "flush-promises"
import { createMockClient } from "mock-apollo-client"
import { UPDATE_PUBLICATION_BASICS } from "src/graphql/mutations"
import { ref as mockRef } from "vue"
import BasicPage from "./BasicPage.vue"

import { beforeEach, describe, expect, test, vi } from "vitest"

vi.mock("src/use/forms", async (importOriginal) => {
  const forms = await importOriginal()
  return {
    ...forms,
    useDirtyGuard: () => { },
    useFormState: () => ({
      dirty: mockRef(false),
      saved: mockRef(false),
      state: mockRef("idle"),
      queryLoading: mockRef(false),
      mutationLoading: mockRef(false),
      errorMessage: mockRef(""),
      mutationError: mockRef({}),
    }),
  }
})

installQuasarPlugin()
describe("BasicPage", () => {
  const mockClient = createMockClient()
  const makeWrapper = async () => {
    const wrapper = mount(BasicPage, {
      global: {
        provide: {
          [ApolloClients]: { default: mockClient },
        },
        mocks: {
          $t: (t) => t,
        },
        stubs: ["update-basic-form"],
      },
      props: {
        publication: {
          id: "1",
          name: "Test Name",
          is_publicly_available: false,
        },
      },
    })
    await flushPromises()
    return wrapper
  }

  const mutateHandler = vi.fn()
  mockClient.setRequestHandler(UPDATE_PUBLICATION_BASICS, mutateHandler)

  beforeEach(() => {
    vi.resetAllMocks()
  })

  test("able to mount", async () => {
    const wrapper = await makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  test("saves publication data", async () => {
    const newData = {
      name: "new name",
      is_publicly_visible: true,
    }

    mutateHandler.mockResolvedValue({
      data: { updatePublication: { id: "1", ...newData } },
    })

    const wrapper = await makeWrapper()
    await wrapper.findComponent({ ref: "form" }).vm.$emit("save", newData)

    expect(mutateHandler).toHaveBeenCalledWith({
      id: "1",
      ...newData,
    })
  })

  test("sets error on failure", async () => {
    mutateHandler.mockRejectedValue({})

    const wrapper = await makeWrapper()
    await wrapper.findComponent({ ref: "form" }).vm.$emit("save", {})
    await flushPromises()

    expect(wrapper.vm.formState.errorMessage.value).not.toBe("")
  })
})

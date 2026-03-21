import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { UPDATE_PUBLICATION_BASICS } from "src/graphql/mutations"
import type { UpdatePublicationBasicsMutation } from "src/graphql/generated/graphql"
import { ref as mockRef } from "vue"
import BasicPage from "./BasicPage.vue"

import { beforeEach, describe, expect, test, vi } from "vitest"

vi.mock("src/use/forms", async (importOriginal) => {
  const forms = await importOriginal<typeof import("src/use/forms")>()
  return {
    ...forms,
    useDirtyGuard: () => {},
    useFormState: () => ({
      dirty: mockRef(false),
      saved: mockRef(false),
      state: mockRef("idle"),
      queryLoading: mockRef(false),
      mutationLoading: mockRef(false),
      errorMessage: mockRef(""),
      mutationError: mockRef({})
    })
  }
})

installQuasarPlugin()
const mockClient = installApolloClient()

describe("BasicPage", () => {
  const makeWrapper = async () => {
    const wrapper = mount(BasicPage, {
      global: {
        stubs: ["update-basic-form"]
      },
      props: {
        publication: {
          id: "1",
          name: "Test Name",
          is_publicly_visible: false,
          is_accepting_submissions: true
        } as any
      }
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
      is_accepting_submissions: true
    }

    const mockResponse: { data: UpdatePublicationBasicsMutation } = {
      data: { updatePublication: { id: "1", ...newData } }
    }
    mutateHandler.mockResolvedValue(mockResponse)

    const wrapper = await makeWrapper()
    await wrapper.findComponent({ ref: "form" }).vm.$emit("save", newData)

    expect(mutateHandler).toHaveBeenCalledWith({
      id: "1",
      ...newData
    })
  })

  test("sets error on failure", async () => {
    mutateHandler.mockRejectedValue({})

    const wrapper = await makeWrapper()
    await wrapper.findComponent({ ref: "form" }).vm.$emit("save", {})
    await flushPromises()

    expect((wrapper.vm as any).formState.errorMessage.value).not.toBe("")
  })
})

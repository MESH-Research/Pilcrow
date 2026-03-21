import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { UPDATE_USER } from "src/graphql/mutations"
import { CURRENT_USER } from "src/graphql/queries"
import type {
  CurrentUserQuery,
  UpdateUserMutation
} from "src/graphql/generated/graphql"
import { ref as mockRef } from "vue"
import SettingsPage from "./SettingsPage.vue"

import { beforeEach, describe, expect, it, test, vi } from "vitest"

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
      errorMessage: mockRef("")
    })
  }
})

vi.mock("quasar", async (importOriginal) => {
  const quasar = await importOriginal<typeof import("quasar")>()
  return {
    ...quasar,
    useQuasar: () => ({
      notify: vi.fn()
    })
  }
})

installQuasarPlugin()
const mockClient = installApolloClient()

describe("Settings page", () => {
  const makeWrapper = async () => {
    const wrapper = mount(SettingsPage)
    await flushPromises()
    return wrapper
  }

  beforeEach(() => {
    vi.resetAllMocks()
    const mockCurrentUser: { data: CurrentUserQuery } = {
      data: {
        currentUser: {
          id: "1",
          username: "test",
          name: "TestDoe",
          email: "test@example.com",
          email_verified_at: null,
          roles: []
        }
      }
    }
    requestHandler.mockResolvedValue(mockCurrentUser)
  })

  const mutateHandler = vi.fn()
  const requestHandler = vi.fn()
  mockClient.setRequestHandler(UPDATE_USER, mutateHandler)
  mockClient.setRequestHandler(CURRENT_USER, requestHandler)

  const accountData = () => ({
    id: "1",
    username: "username1",
    name: "Test User",
    email: "testemail@example.com"
  })

  beforeEach(() => {
    vi.resetAllMocks()
  })

  it("mounts without errors", async () => {
    const wrapper = await makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  test("saves account data", async () => {
    const initialData = accountData()
    const newData = accountData()
    newData.username = "Tester User"

    requestHandler.mockResolvedValue({ data: { currentUser: initialData } })
    const mockUpdateResponse: { data: UpdateUserMutation } = {
      data: { updateUser: { ...newData, updated_at: "soonish" } }
    }
    mutateHandler.mockResolvedValue(mockUpdateResponse)
    const wrapper = await makeWrapper()
    await wrapper.findComponent({ ref: "form" }).vm.$emit("save", newData)

    expect(requestHandler).toHaveBeenCalledTimes(1)
    expect(mutateHandler).toHaveBeenCalledWith({
      ...newData
    })
  })

  test("sets error on failure", async () => {
    const formData = accountData()
    requestHandler.mockResolvedValue({ data: { currentUser: formData } })
    mutateHandler.mockRejectedValue({})

    const wrapper = await makeWrapper()

    await wrapper.findComponent({ ref: "form" }).vm.$emit("save", formData)
    await flushPromises()

    expect((wrapper.vm as any).formState.errorMessage.value).not.toBe("")
  })
})

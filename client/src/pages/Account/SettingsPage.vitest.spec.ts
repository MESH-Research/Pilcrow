import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { UPDATE_USER } from "src/graphql/mutations"
import { CURRENT_USER } from "src/graphql/queries"
import {
  ResetDismissedUiDocument,
  UpdateUserPreferencesDocument,
  UserThemePreference
} from "src/graphql/generated/graphql"
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

// Captured by `vi.mock` factory below; tests reach in to assert
// dialog options or trigger the onOk callback for reset confirmation.
const dialogMock = vi.fn()

vi.mock("quasar", async (importOriginal) => {
  const quasar = await importOriginal<typeof import("quasar")>()
  return {
    ...quasar,
    useQuasar: () => ({
      notify: vi.fn(),
      dialog: dialogMock
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
          roles: [],
          preferences: null,
          dismissed_ui: [],
          feature_opt_ins: []
        }
      }
    }
    requestHandler.mockResolvedValue(mockCurrentUser)
  })

  const mutateHandler = vi.fn()
  const requestHandler = vi.fn()
  const updatePreferencesHandler = vi.fn()
  const resetDismissedHandler = vi.fn()
  mockClient.setRequestHandler(UPDATE_USER, mutateHandler)
  mockClient.setRequestHandler(CURRENT_USER, requestHandler)
  mockClient.setRequestHandler(
    UpdateUserPreferencesDocument,
    updatePreferencesHandler
  )
  mockClient.setRequestHandler(ResetDismissedUiDocument, resetDismissedHandler)

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

  describe("preferences", () => {
    function preferenceMutationResponse(
      theme: UserThemePreference | null,
      a11yColorPatterns: boolean | null
    ) {
      return {
        data: {
          updateUserPreferences: {
            __typename: "User",
            id: "1",
            preferences: {
              __typename: "UserPreferences",
              theme,
              a11y_color_patterns: a11yColorPatterns
            }
          }
        }
      }
    }

    test("clicking the DARK theme radio fires updatePreferences with that theme", async () => {
      updatePreferencesHandler.mockResolvedValue(
        preferenceMutationResponse(UserThemePreference.DARK, null)
      )

      const wrapper = await makeWrapper()
      // Three theme radios render with the same data-cy; the third
      // entry is DARK per the themeOptions array order in the page.
      const radios = wrapper.findAll("[data-cy=theme_option]")
      expect(radios.length).toBe(3)
      await radios[2].trigger("click")
      await flushPromises()

      expect(updatePreferencesHandler).toHaveBeenCalledTimes(1)
      expect(updatePreferencesHandler).toHaveBeenCalledWith({
        input: { theme: UserThemePreference.DARK }
      })
    })

    test("toggling accessible color patterns fires updatePreferences with the new boolean", async () => {
      updatePreferencesHandler.mockResolvedValue(
        preferenceMutationResponse(null, true)
      )

      const wrapper = await makeWrapper()
      const toggle = wrapper.find("[data-cy=a11y_color_patterns_toggle]")
      expect(toggle.exists()).toBe(true)
      await toggle.trigger("click")
      await flushPromises()

      expect(updatePreferencesHandler).toHaveBeenCalledTimes(1)
      expect(updatePreferencesHandler).toHaveBeenCalledWith({
        input: { a11y_color_patterns: true }
      })
    })
  })

  describe("reset dismissed elements", () => {
    test("button is disabled when there is nothing to reset", async () => {
      // Default mock has dismissed_ui: [] so the button should be disabled.
      const wrapper = await makeWrapper()
      const btn = wrapper.find("[data-cy=reset_dismissed_btn]")
      expect(btn.exists()).toBe(true)
      // Quasar disables buttons via aria-disabled rather than the
      // native disabled attribute.
      expect(btn.attributes("aria-disabled")).toBe("true")
      expect(dialogMock).not.toHaveBeenCalled()
    })

    test("button enables and opens a confirm dialog when keys are present", async () => {
      requestHandler.mockResolvedValue({
        data: {
          currentUser: {
            __typename: "User",
            id: "1",
            username: "test",
            name: "TestDoe",
            display_label: "TestDoe",
            email: "test@example.com",
            email_verified_at: null,
            highest_privileged_role: null,
            roles: [],
            preferences: null,
            dismissed_ui: ["manage_ui.opt_in_callout"],
            feature_opt_ins: []
          }
        }
      })
      const onOk = vi.fn().mockReturnThis()
      dialogMock.mockReturnValue({
        onOk,
        onCancel: vi.fn().mockReturnThis(),
        onDismiss: vi.fn().mockReturnThis()
      })

      const wrapper = await makeWrapper()
      await flushPromises()
      const btn = wrapper.find("[data-cy=reset_dismissed_btn]")
      expect(btn.attributes("aria-disabled")).not.toBe("true")
      await btn.trigger("click")

      expect(dialogMock).toHaveBeenCalledTimes(1)
      expect(onOk).toHaveBeenCalledTimes(1)
    })

    test("confirming the dialog fires the resetDismissedUi mutation", async () => {
      requestHandler.mockResolvedValue({
        data: {
          currentUser: {
            __typename: "User",
            id: "1",
            username: "test",
            name: "TestDoe",
            email: "test@example.com",
            email_verified_at: null,
            highest_privileged_role: null,
            display_label: "TestDoe",
            roles: [],
            preferences: null,
            dismissed_ui: ["x"],
            feature_opt_ins: []
          }
        }
      })

      // Capture the onOk callback so we can invoke it after the
      // wrapper is mounted, simulating the user confirming.
      let okCallback: (() => unknown) | null = null
      dialogMock.mockReturnValue({
        onOk: (cb: () => unknown) => {
          okCallback = cb
          return {
            onCancel: vi.fn().mockReturnThis(),
            onDismiss: vi.fn().mockReturnThis()
          }
        }
      })
      resetDismissedHandler.mockResolvedValue({
        data: {
          resetDismissedUi: {
            __typename: "User",
            id: "1",
            dismissed_ui: []
          }
        }
      })

      const wrapper = await makeWrapper()
      await wrapper.find("[data-cy=reset_dismissed_btn]").trigger("click")
      expect(okCallback).toBeTypeOf("function")

      await okCallback!()
      await flushPromises()

      expect(resetDismissedHandler).toHaveBeenCalledTimes(1)
    })
  })
})

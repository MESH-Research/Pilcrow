import { mount } from "vue-composable-tester"
import {
  useDirtyGuard,
  useFormState,
  useGraphQLValidation,
  useVQWrap,
} from "./forms"
import { provide, ref } from "vue"

let dirtyGuardCallback = null

jest.mock("vue-router", () => ({
  onBeforeRouteLeave: jest.fn((cb) => {
    dirtyGuardCallback = cb
  }),
}))

const mockEmit = jest.fn()

jest.mock("vue", () => ({
  ...jest.requireActual("vue"),
  getCurrentInstance: () => ({
    emit: mockEmit,
  }),
}))

const mockDialog = jest.fn()
jest.mock("quasar", () => ({
  ...jest.requireActual("quasar"),
  useQuasar: () => ({
    dialog: mockDialog,
  }),
}))

describe("useDirtyGuard composable", () => {
  test("allows a clean navigation to continue", () => {
    const dirty = ref(false)

    mount(() => useDirtyGuard(dirty))

    const mockNext = jest.fn()
    dirtyGuardCallback(null, null, mockNext)
    expect(mockNext).toHaveBeenCalledTimes(1)
    expect(mockNext).toHaveBeenCalledWith()
  })

  test("Shows dialog appropriatly and correctly responds to user feedback", () => {
    const dirty = ref(true)
    let okCallback, cancelCallback
    const dialogReturn = {
      onOk: (okCb) => {
        okCallback = okCb
        return dialogReturn
      },
      onCancel: (cancelCb) => {
        cancelCallback = cancelCb
        return dialogReturn
      },
    }
    mockDialog.mockImplementation(() => dialogReturn)

    mount(() => useDirtyGuard(dirty))
    const mockNext = jest.fn()
    dirtyGuardCallback(null, null, mockNext)

    expect(mockDialog).toBeCalledTimes(1)
    okCallback()
    expect(mockNext).toHaveBeenCalledTimes(1)
    expect(mockNext).toHaveBeenCalledWith()

    mockNext.mockReset()

    cancelCallback()
    expect(mockNext).toHaveBeenCalledTimes(1)
    expect(mockNext).toHaveBeenCalledWith(false)

    mockNext.mockReset()
    dirty.value = false
    dirtyGuardCallback(null, null, mockNext)
    expect(mockNext).toHaveBeenCalledTimes(1)
    expect(mockNext).toBeCalledWith()
  })

  test("sets and removes window handlers", () => {
    let callBackFn
    const dirty = ref(false)
    window.addEventListener = jest.fn((_, callback) => {
      callBackFn = callback
    })
    window.removeEventListener = jest.fn()
    const mockEvent = {
      preventDefault: jest.fn(),
    }

    const { unmount } = mount(() => useDirtyGuard(dirty))

    //should add an eventlistener
    expect(window.addEventListener).toHaveBeenCalledTimes(1)
    expect(window.addEventListener).toHaveBeenCalledWith(
      "beforeunload",
      expect.any(Function)
    )

    //Test event callback if not dirty
    callBackFn(mockEvent)
    expect(mockEvent.preventDefault).toHaveBeenCalledTimes(0)

    //Test event callback if is dirty
    dirty.value = true
    callBackFn(mockEvent)
    expect(mockEvent.preventDefault).toHaveBeenCalledTimes(1)

    //Check that event listeners are removed on unmount
    unmount()
    expect(window.removeEventListener).toHaveBeenCalledTimes(1)
    expect(window.removeEventListener).toHaveBeenCalledWith(
      "beforeunload",
      callBackFn
    )
  })
})

describe("useFormState composable", () => {
  test("form states", () => {
    const queryLoading = ref(false),
      mutationLoading = ref(false)
    const { result } = mount(() => useFormState(queryLoading, mutationLoading))
    const { state, saved, dirty, errorMessage } = result

    expect(state.value).toBe("idle")
    queryLoading.value = true

    expect(state.value).toBe("loading")

    mutationLoading.value = true
    expect(state.value).toBe("saving")

    saved.value = true
    expect(state.value).toBe("saving")

    mutationLoading.value = false
    queryLoading.value = false
    expect(state.value).toBe("saved")

    saved.value = false
    dirty.value = true
    expect(state.value).toBe("dirty")

    dirty.value = false
    errorMessage.value = "error message"
    expect(state.value).toBe("error")
  })
})

describe("useVQWrap composable", () => {
  const validator = {
    $model: "",
    $path: "testField",
  }
  test("without vqwrap provides present", () => {
    const { result } = mount(() => useVQWrap(validator, false))

    const { getTranslationKey, model } = result

    expect(getTranslationKey("label")).toBe("testField.label")

    model.value = "new Value"
    expect(mockEmit).toHaveBeenCalledTimes(1)
    expect(mockEmit).toHaveBeenCalledWith("vqupdate", validator, "new Value")
  })

  test("update function provider", () => {
    const mockUpdate = jest.fn()
    const { result } = mount(() => useVQWrap(validator, false), {
      provider: () => {
        provide("vqupdate", mockUpdate)
      },
    })
    const { model } = result

    model.value = "parent update"
    expect(mockUpdate).toHaveBeenCalledTimes(1)
    expect(mockUpdate).toHaveBeenCalledWith(validator, "parent update")
  })
  test("provided prefix is used", () => {
    const { result } = mount(() => useVQWrap(validator, false), {
      provider: () => {
        provide("tPrefix", "parentPrefix")
      },
    })
    const { getTranslation } = result

    expect(getTranslation("error")).toBe("parentPrefix.testField.error")
  })

  test("local translation path overrides prefix", () => {
    const { result } = mount(() => useVQWrap(validator, "localPath"), {
      provider: () => {
        provide("tPrefix", "parentPrefix")
      },
    })
    const { getTranslation } = result

    expect(getTranslation("error")).toBe("localPath.error")
  })
})

describe("useGraphQLValidation composable", () => {
  const error = ref({
    graphQLErrors: [
      {
        message: "Validation failed for the field [updateUser].",
        extensions: {
          validation: {
            "user.username": ["USERNAME_IN_USE"],
          },
          category: "validation",
        },
        locations: [
          {
            line: 2,
            column: 3,
          },
        ],
        path: ["updateUser"],
      },
      {
        message: "Something else happened",
      },
    ],
    clientErrors: [],
    networkError: null,
    message: "Validation failed for the field [updateUser].",
  })

  test("correctly extracts only validation errors", () => {
    const { result } = mount(() => useGraphQLValidation(error))

    const { hasValidationErrors, validationErrors } = result

    expect(hasValidationErrors.value).toBe(true)
    expect(validationErrors.value).toEqual({
      user: { username: ["USERNAME_IN_USE"] },
    })

    error.value = {}

    expect(hasValidationErrors.value).toBe(false)
    expect(validationErrors.value).toEqual({})
  })
})

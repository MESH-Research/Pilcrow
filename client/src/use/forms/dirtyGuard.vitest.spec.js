import { mount } from "@vue/test-utils"
import { useDirtyGuard } from "./dirtyGuard"
import { ref, defineComponent, h } from "vue"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { onUnmounted } from "vue"
import { Dialog } from "app/test/vitest/mockedPlugins"
import { onBeforeRouteLeave } from "vue-router"

import { describe, test, vi, expect } from "vitest"

vi.mock("vue-router", async (importOriginal) => {
  const original = await importOriginal()
  const onBeforeRouteLeave = vi.fn()
  return {
    ...original,
    onBeforeRouteLeave
  }
})

vi.mock("vue", async (importOriginal) => {
  const original = await importOriginal()
  const onUnmounted = vi.fn()
  return {
    ...original,
    onUnmounted
  }
})

installQuasarPlugin({ plugins: { Dialog } })

describe("useDirtyGuard composable", () => {
  const factory = (dirtyRef) =>
    defineComponent({
      setup() {
        useDirtyGuard(dirtyRef)
      },
      render: () => h("div")
    })

  test("allows a clean navigation to continue", async () => {
    const dirty = ref(false)
    let callback
    onBeforeRouteLeave.mockImplementation((cb) => (callback = cb))

    mount(factory(dirty))
    expect(await callback()).toBe(true)
  })

  test("Shows dialog appropriately and correctly responds to user feedback", async () => {
    let dirtyGuardCallback
    onBeforeRouteLeave.mockImplementation((cb) => (dirtyGuardCallback = cb))

    const dirty = ref(true)
    mount(factory(dirty))

    Dialog.resolveOk()

    await expect(dirtyGuardCallback()).resolves.toBe(true)
    expect(Dialog.dialog).toHaveBeenCalledTimes(1)

    Dialog.resolveCancel()

    await expect(dirtyGuardCallback()).resolves.toBe(false)
    expect(Dialog.dialog).toHaveBeenCalledTimes(2)

    dirty.value = false

    await expect(dirtyGuardCallback()).resolves.toBe(true)
    expect(Dialog.dialog).toHaveBeenCalledTimes(2)
  })

  test("sets and removes window handlers", () => {
    let callBackFn
    const dirty = ref(false)
    window.addEventListener = vi.fn((_, callback) => {
      callBackFn = callback
    })
    window.removeEventListener = vi.fn()
    const mockEvent = {
      preventDefault: vi.fn()
    }
    let unmountCb
    onUnmounted.mockImplementation((cb) => (unmountCb = cb))

    mount(factory(dirty))
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
    unmountCb()
    expect(window.removeEventListener).toHaveBeenCalledTimes(1)
    expect(window.removeEventListener).toHaveBeenCalledWith(
      "beforeunload",
      callBackFn
    )
  })
})

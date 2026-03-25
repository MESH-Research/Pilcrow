import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { useFormState, formStateKey } from "src/use/forms"
import { ref as mockRef, nextTick } from "vue"
import FormActions from "./FormActions.vue"

import { describe, expect, test, vi } from "vitest"

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
const formState = useFormState(
  { loading: mockRef(false) },
  { loading: mockRef(false), error: mockRef(null) }
)
installQuasarPlugin()
describe("Formactions", () => {
  const factory = (props = {}) => {
    return mount(FormActions, {
      global: {
        provide: {
          [formStateKey]: formState
        }
      },
      propsData: {
        ...props
      }
    })
  }
  test("wrapper is valid", async () => {
    const wrapper = factory()

    expect(wrapper).toBeTruthy()
  })

  test("hidden when form is idle", () => {
    const wrapper = factory()
    ;(wrapper.vm as any).state = "idle"
    expect(wrapper.findComponent("q-page-sticky").exists()).toBe(false)
  })

  test("dirty form state", async () => {
    const wrapper = factory()
    ;(formState.state as any).value = "dirty"
    await nextTick()
    expect(wrapper.findComponent("q-page-sticky").exists()).toBe(true)
  })
})

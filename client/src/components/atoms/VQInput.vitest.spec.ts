import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import { useVuelidate } from "@vuelidate/core"
import { useFormState, formStateKey } from "src/use/forms"
import { describe, expect, test, vi } from "vitest"
import { ref, nextTick, reactive } from "vue"
import VQInput from "./VQInput.vue"
import type { VuelidateValidator } from "src/types/vuelidate"

installQuasarPlugin()

const queryRef = ref(false)

const factory = (
  props: { v: VuelidateValidator; t?: string | boolean },
  provide: Record<string, unknown> = {}
) => {
  return mount(VQInput, {
    global: {
      provide: {
        [formStateKey]: useFormState(
          { loading: queryRef },
          { loading: ref(false) }
        ),
        ...provide
      }
    },
    props
  })
}

const vuelidateStub = {
  $model: "",
  $path: "field",
  $error: false
} as VuelidateValidator

describe("VQInput", () => {
  test("emits update event", () => {
    const form = reactive({ field: "test" })
    const v$ = useVuelidate({ field: {} }, form)
    const wrapper = factory({ v: v$.value.field })

    const input = wrapper.find("input")
    input.setValue("tstvalue")
    const event = wrapper.emitted("vqupdate")[0]

    expect(event[0]).toEqual(v$.value.field)
    expect(event[1]).toBe("tstvalue")
  })

  test("prefixes translations", () => {
    const form = reactive({ field: "test" })
    const v$ = useVuelidate({ field: {} }, form)
    const wrapper = factory({ v: v$.value.field, t: "field" })
    const qinput = wrapper.findComponent({ name: "q-input" })

    expect(qinput.props("hint")).toBe("field.hint")
    expect(qinput.props("label")).toBe("field.label")
  })

  test("prefixes translations using provided prefix", () => {
    const stub = vuelidateStub
    const wrapper = factory({ v: stub }, { tPrefix: "myPrefix" })

    const qinput = wrapper.findComponent({ name: "q-input" })

    expect(qinput.props("hint")).toBe("myPrefix.field.hint")
    expect(qinput.props("label")).toBe("myPrefix.field.label")
  })

  test("uses provided update function", () => {
    const stub = reactive(vuelidateStub)
    const updater = vi.fn()
    const wrapper = factory({ v: stub }, { vqupdate: updater })

    const input = wrapper.find("input")
    input.setValue("tstValue")

    expect(updater).toHaveBeenCalledTimes(1)
    expect(updater).toHaveBeenCalledWith(stub, "tstValue")
  })

  test("shows skeleton component from provided formState", async () => {
    const stub = reactive(vuelidateStub)

    const wrapper = factory({ v: stub })

    expect(wrapper.findComponent({ name: "q-input" }).exists()).toBe(true)

    queryRef.value = true
    await nextTick()

    expect(wrapper.findComponent({ name: "q-input" }).exists()).toBe(false)
    expect(wrapper.findComponent({ name: "q-skeleton" }).exists()).toBe(true)
  })
})

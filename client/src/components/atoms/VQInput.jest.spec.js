import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { useVuelidate } from "@vuelidate/core"
import { reactive } from "vue"
import { nextTick } from "vue"
import VQInput from "./VQInput"
import { ref as mockRef } from "vue"
import { useFormState } from "src/use/forms"

jest.mock("vue-i18n", () => ({
  useI18n: () => ({
    te: (t) => t,
  }),
}))

jest.mock("src/use/forms", () => ({
  useDirtyGuard: () => {},
  useFormState: () => ({
    dirty: mockRef(false),
    saved: mockRef(false),
    state: mockRef("idle"),
    queryLoading: mockRef(false),
    mutationLoading: mockRef(false),
    errorMessage: mockRef(""),
  }),
}))

installQuasarPlugin()
const factory = (props, provide) => {
  return mount(VQInput, {
    global: {
      provide: {
        formState: useFormState(),
        ...provide,
      },
      mocks: {
        $t: (token) => token,
        $te: () => true,
      },
    },
    props: {
      ...props,
    },
  })
}

const vuelidateStub = {
  $model: "",
  $path: "field",
  $error: false,
}

describe("VQInput", () => {
  test("emits update event", () => {
    const form = reactive({ field: "test" })
    const v$ = useVuelidate({ field: {} }, form)
    const wrapper = factory({ v: v$.value.field })

    const input = wrapper.find("input")
    input.setValue("tstvalue")
    const event = wrapper.emitted("vqupdate")[0]

    expect(event[0]).toEqual(v$.value.field)
    expect(event[1]).toEqual("tstvalue")
  })

  test("prefixes translations", () => {
    const form = reactive({ field: "test" })
    const v$ = useVuelidate({ field: {} }, form)
    const wrapper = factory({ v: v$.value.field, t: "field" })
    const qinput = wrapper.findComponent({ name: "q-input" })

    expect(qinput.props("hint")).toEqual("field.hint")
    expect(qinput.props("label")).toEqual("field.label")
  })

  test("prefixes translations using provided prefix", () => {
    const stub = vuelidateStub
    const wrapper = factory({ v: stub }, { tPrefix: "myPrefix" })

    const qinput = wrapper.findComponent({ name: "q-input" })

    expect(qinput.props("hint")).toEqual("myPrefix.field.hint")
    expect(qinput.props("label")).toEqual("myPrefix.field.label")
  })

  test("uses provided update function", () => {
    const stub = reactive(vuelidateStub)
    const updater = jest.fn()
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

    wrapper.vm.parentState = "loading"
    await nextTick()

    expect(wrapper.findComponent({ name: "q-input" }).exists()).toBe(false)
    expect(wrapper.findComponent({ name: "q-skeleton" }).exists()).toBe(true)
  })
})

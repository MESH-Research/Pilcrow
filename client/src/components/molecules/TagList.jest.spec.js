import TagList from "./TagList.vue"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"

installQuasarPlugin()
describe("TagList Component", () => {
  const factory = (modelValue, addProps = {}) => {
    return mount(TagList, {
      global: {
        mocks: {
          $t: (token) => token,
        },
      },
      props: {
        modelValue,
        ...addProps,
      },
    })
  }

  it("mounts without errors", () => {
    const wrapper = factory([])
    expect(wrapper).toBeTruthy()
  })

  it("renders list items", async () => {
    const wrapper = factory(["a", "b", "c"])

    expect(wrapper.findAllComponents({ name: "q-chip" })).toHaveLength(3)
  })

  it("adds items to list", async () => {
    const wrapper = factory([])
    const inputWrapper = wrapper.findComponent({ name: "q-input" })

    await inputWrapper.setValue("newItem")
    await wrapper.findComponent({ ref: "addBtn" }).trigger("click")

    expect(wrapper.emitted("update:modelValue")[0][0]).toHaveLength(1)
    expect(wrapper.emitted("update:modelValue")[0][0]).toEqual(["newItem"])

    await wrapper.setProps({ modelValue: ["item"] })
    await inputWrapper.setValue("another new item")
    await wrapper.findComponent({ ref: "addBtn" }).trigger("click")

    expect(wrapper.emitted("update:modelValue")[1][0]).toHaveLength(2)
    expect(wrapper.emitted("update:modelValue")[1][0]).toEqual([
      "item",
      "another new item",
    ])
  })

  it("deletes list items", async () => {
    const wrapper = factory(["a", "b", "c"])

    const items = wrapper.findAllComponents({ name: "q-chip" })
    expect(items).toHaveLength(3)

    await items.at(1).find("i").trigger("click")
    expect(wrapper.emitted("update:modelValue")[0][0]).toEqual(["a", "c"])

    await items.at(0).find("i").trigger("click")
    expect(wrapper.emitted("update:modelValue")[1][0]).toEqual(["b", "c"])

    await items.at(2).find("i").trigger("click")
    expect(wrapper.emitted("update:modelValue")[2][0]).toEqual(["a", "b"])
  })

  it("does not add duplicates", async () => {
    const wrapper = factory(["a", "b", "c"], { allowDuplicates: false })

    await wrapper.findComponent({ name: "q-input" }).setValue("a")
    await wrapper.findComponent({ ref: "addBtn" }).trigger("click")

    expect(wrapper.emitted("update:modelValue")).toBeUndefined()
  })

  it("can allow duplicates", async () => {
    const wrapper = factory(["a", "b", "c"], { allowDuplicates: true })

    await wrapper.findComponent({ name: "q-input" }).setValue("a")
    await wrapper.findComponent({ ref: "addBtn" }).trigger("click")

    expect(wrapper.emitted("update:modelValue")[0][0]).toEqual([
      "a",
      "b",
      "c",
      "a",
    ])
  })
})

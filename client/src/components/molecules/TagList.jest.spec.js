import { mountQuasar } from "@quasar/quasar-app-extension-testing-unit-jest"
import TagList from "./TagList.vue"
import { QChip, QInput, QBtn, QIcon } from "quasar"

describe("EditableList Component", () => {
  const factory = (value, addProps = {}) => {
    return mountQuasar(TagList, {
      quasar: {
        components: { QInput, QChip, QBtn, QIcon },
      },
      mount: {
        type: "full",
        mocks: {
          $t: (token) => token,
        },
        propsData: {
          value,
          ...addProps,
        },
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
    const inputWrapper = wrapper.find("input")

    await inputWrapper.setValue("newItem")
    await wrapper.findComponent({ ref: "addBtn" }).trigger("click")

    expect(wrapper.emitted("input")[0][0]).toHaveLength(1)
    expect(wrapper.emitted("input")[0][0]).toEqual(["newItem"])

    await wrapper.setProps({ value: ["item"] })
    await inputWrapper.setValue("another new item")
    await wrapper.findComponent({ ref: "addBtn" }).trigger("click")

    expect(wrapper.emitted("input")[1][0]).toHaveLength(2)
    expect(wrapper.emitted("input")[1][0]).toEqual(["item", "another new item"])
  })

  it("deletes list items", async () => {
    const wrapper = factory(["a", "b", "c"])

    const items = wrapper.findAllComponents({ name: "q-chip" })
    expect(items).toHaveLength(3)

    await items.at(1).find("i").trigger("click")
    expect(wrapper.emitted("input")[0][0]).toEqual(["a", "c"])

    await items.at(0).find("i").trigger("click")
    expect(wrapper.emitted("input")[1][0]).toEqual(["b", "c"])

    await items.at(2).find("i").trigger("click")
    expect(wrapper.emitted("input")[2][0]).toEqual(["a", "b"])
  })

  it("does not add duplicates", async () => {
    const wrapper = factory([])

    await wrapper.setProps({ value: ["a", "b", "c"], allowDuplicates: false })

    await wrapper.find("input").setValue("a")
    await wrapper.findComponent({ ref: "addBtn" }).trigger("click")

    expect(wrapper.emitted("input")).toBeUndefined()
  })

  it("can allow duplicates", async () => {
    const wrapper = factory(["a", "b", "c"], { allowDuplicates: true })

    await wrapper.find("input").setValue("a")
    await wrapper.findComponent({ ref: "addBtn" }).trigger("click")

    expect(wrapper.emitted("input")[0][0]).toEqual(["a", "b", "c", "a"])
  })
})

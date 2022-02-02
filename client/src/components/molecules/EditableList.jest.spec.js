import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { mount } from "@vue/test-utils"
import EditableList from "./EditableList.vue"
installQuasarPlugin()
describe("EditableList Component", () => {
  const factory = (modelValue, addProps = {}) => {
    return mount(EditableList, {
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

  const findByAria = (wrapper, label) => {
    return wrapper.find(`[aria-label="${label}"]`)
  }

  it("mounts without errors", () => {
    const wrapper = factory([])
    expect(wrapper).toBeTruthy()
  })

  it("renders list items", async () => {
    const wrapper = factory(["a", "b", "c"])

    expect(wrapper.findAllComponents({ name: "q-item" })).toHaveLength(3)
  })

  it("adds items to list", async () => {
    const wrapper = factory([])
    const inputWrapper = wrapper.findComponent({ name: "q-input" })
    const addBtn = wrapper.findComponent({ ref: "addBtn" })
    await inputWrapper.setValue("newItem")
    await addBtn.trigger("click")

    expect(wrapper.emitted("update:modelValue")[0][0]).toHaveLength(1)
    expect(wrapper.emitted("update:modelValue")[0][0]).toEqual(["newItem"])

    await wrapper.setProps({ modelValue: ["item"] })
    await inputWrapper.setValue("another new item")
    await addBtn.trigger("click")

    expect(wrapper.emitted("update:modelValue")[1][0]).toHaveLength(2)
    expect(wrapper.emitted("update:modelValue")[1][0]).toEqual([
      "item",
      "another new item",
    ])
  })

  it("deletes list items", async () => {
    const wrapper = factory(["a", "b", "c"])

    const items = wrapper.findAllComponents({ name: "q-item" })
    expect(items).toHaveLength(3)

    await findByAria(items.at(1), "lists.delete").trigger("click")
    expect(wrapper.emitted("update:modelValue")[0][0]).toEqual(["a", "c"])

    await findByAria(items.at(0), "lists.delete").trigger("click")
    expect(wrapper.emitted("update:modelValue")[1][0]).toEqual(["b", "c"])

    await findByAria(items.at(2), "lists.delete").trigger("click")
    expect(wrapper.emitted("update:modelValue")[2][0]).toEqual(["a", "b"])
  })

  it("moves items", async () => {
    const wrapper = factory(["a", "b", "c"])

    const items = wrapper.findAllComponents({ name: "q-item" })

    await findByAria(items.at(0), "lists.move_down").trigger("click")
    expect(wrapper.emitted("update:modelValue")[0][0]).toEqual(["b", "a", "c"])

    await findByAria(items.at(0), "lists.move_up").trigger("click")
    expect(wrapper.emitted("update:modelValue")).toHaveLength(1)

    await findByAria(items.at(2), "lists.move_down").trigger("click")
    expect(wrapper.emitted("update:modelValue")).toHaveLength(1)

    await findByAria(items.at(2), "lists.move_up").trigger("click")
    expect(wrapper.emitted("update:modelValue")[1][0]).toEqual(["a", "c", "b"])
  })

  it("edits items", async () => {
    const wrapper = factory(["a", "b", "c"])

    const items = wrapper.findAllComponents({ name: "q-item" })
    await findByAria(items.at(1), "lists.edit").trigger("click")

    await items.at(1).find("input").setValue("d")
    await findByAria(items.at(1), "lists.save").trigger("click")

    expect(wrapper.emitted("update:modelValue")[0][0]).toEqual(["a", "d", "c"])
  })

  test("label click triggers edit", async () => {
    const wrapper = factory(["a", "b", "c"])

    const items = wrapper.findAllComponents({ name: "q-item" })
    await items.at(1).findComponent({ name: "q-item-label" }).trigger("click")

    expect(items.at(1).findAll("input")).toHaveLength(1)
  })

  it("does not add duplicates", async () => {
    const wrapper = factory(["a", "b", "c"], { allowDuplicates: false })

    await wrapper.findComponent({ name: "q-input" }).setValue("a")
    await wrapper.findComponent({ ref: "addBtn" }).trigger("click")

    expect(wrapper.emitted("update:modelValue")).toBeUndefined()
  })

  it("can allow duplicates", async () => {
    const wrapper = factory(["a", "b", "c"], { allowDuplicates: true })

    await wrapper.find("input").setValue("a")
    await wrapper.findComponent({ ref: "addBtn" }).trigger("click")

    expect(wrapper.emitted("update:modelValue")[0][0]).toEqual([
      "a",
      "b",
      "c",
      "a",
    ])
  })
})

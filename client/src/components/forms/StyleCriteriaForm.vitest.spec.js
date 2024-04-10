import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { Dialog } from "test/vitest/mockedPlugins"
import { useFormState } from "src/use/forms"
import { ref } from "vue"
import StyleCriteriaForm from "./StyleCriteriaForm.vue"

import { describe, expect, test } from "vitest"


installQuasarPlugin({ plugins: { Dialog } })
describe("StyleCriteriaForm", () => {
  const makeWrapper = (criteria) => {
    return mount(StyleCriteriaForm, {
      global: {
        provide: {
          formState: useFormState({ loading: ref(false) }, { loading:  ref(false) }),
        },
        stubs: ["QEditor"],
      },
      props: {
        criteria,
      },
    })
  }

  test("able to mount", () => {
    const wrapper = makeWrapper({})
    expect(wrapper).toBeTruthy()
  })

  test("name field validation errors", async () => {
    const wrapper = makeWrapper({})
    const form = wrapper.findComponent({ name: "QForm" })
    const nameInput = wrapper.findComponent({ ref: "name-input" })

    //Name required
    await form.trigger("submit")
    await flushPromises()
    expect(nameInput.classes("q-field--error")).toBe(true)
    expect(nameInput.text()).toContain("errors.required")

    //Name shorter than 50 chars
    await nameInput.findComponent({ name: "QInput" }).setValue("a".repeat(60))
    await form.trigger("submit")
    await flushPromises()
    expect(nameInput.classes("q-field--error")).toBe(true)
    expect(nameInput.text()).toContain("errors.maxLength")

    //No more errors
    await nameInput.findComponent({ name: "QInput" }).setValue("Test Name")
    await form.trigger("submit")
    await flushPromises()
    expect(nameInput.classes("q-field--error")).toBe(false)
    expect(nameInput.text()).not.toContain("errors")

    //Save event emitted with correct data
    expect(wrapper.emitted("save")).toHaveLength(1)
    expect(wrapper.emitted("save")[0]).toEqual([
      { id: "", name: "Test Name", description: "", icon: "task_alt" },
    ])
  })

  test("description field validation errors", async () => {
    const wrapper = makeWrapper({ name: "Test Name" })
    const form = wrapper.findComponent({ name: "QForm" })
    const descriptionInput = wrapper.findComponent({ ref: "description-input" })

    //Description shorter than 4096 characters
    await descriptionInput.setValue("a".repeat(4097))
    await form.trigger("submit")
    expect(wrapper.find("[data-cy='description-errors']").text()).toContain(
      "errors.maxLength"
    )
    expect(descriptionInput.classes("error")).toBe(true)

    //No more errors
    await descriptionInput.setValue("Some simple description")
    await form.trigger("submit")
    await flushPromises()
    expect(descriptionInput.classes("error")).toBe(false)

    //Save event emitted with correct data
    expect(wrapper.emitted("save")).toHaveLength(1)
    expect(wrapper.emitted("save")[0]).toEqual([
      {
        id: "",
        name: "Test Name",
        description: "Some simple description",
        icon: "task_alt",
      },
    ])
  })

  test("id passed in saved value", async () => {
    const wrapper = makeWrapper({ id: "1" })
    const form = wrapper.findComponent({ name: "QForm" })
    const nameInput = wrapper.findComponent({ ref: "name-input" })
    await nameInput.findComponent({ name: "QInput" }).setValue("Test Name")

    const descriptionInput = wrapper.findComponent({ ref: "description-input" })
    await descriptionInput.setValue("Test Description")

    await form.trigger("submit")
    await flushPromises()
    expect(wrapper.emitted("save")[0]).toEqual([
      {
        id: "1",
        name: "Test Name",
        description: "Test Description",
        icon: "task_alt",
      },
    ])
  })

  test("icon dialog return value", async () => {
    const wrapper = makeWrapper({
      id: "1",
      name: "Test Name",
      description: "",
      icon: "initial_icon",
    })

    Dialog.resolveOk('new-icon')
    await wrapper.findComponent({ ref: "icon-button" }).trigger("click")
    await flushPromises()

    await wrapper.findComponent({ name: "QForm" }).trigger("submit")
    await flushPromises()
    expect(wrapper.emitted("save")[0]).toEqual([
      {
        id: "1",
        name: "Test Name",
        description: "",
        icon: "new-icon",
      },
    ])
  })

  test("delete confirm dialog", async () => {
    const wrapper = makeWrapper({
      id: "1",
      name: "TestName",
      description: "",
      icon: "initial_icon",
    })

    Dialog.resolveCancel()
    await wrapper.findComponent({ ref: "button_delete" }).trigger("click")
    expect(Dialog.dialog).toHaveBeenCalledTimes(1)
    expect(wrapper.emitted("delete")).toBeUndefined()

    Dialog.resolveOk()
    await wrapper.findComponent({ ref: "button_delete" }).trigger("click")
    expect(wrapper.emitted("delete")).toHaveLength(1)
    expect(wrapper.emitted("delete")[0][0].id).toBe("1")
  })
})

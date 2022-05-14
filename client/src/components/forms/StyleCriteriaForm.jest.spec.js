import StyleCriteriaForm from "./StyleCriteriaForm.vue"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { ref as mockRef } from "vue"
import { useFormState } from "src/use/forms"
import flushPromises from "flush-promises"

const mockDialog = jest.fn()
jest.mock("quasar", () => ({
  ...jest.requireActual("quasar"),
  useQuasar: () => ({
    dialog: mockDialog,
  }),
}))

jest.mock("src/use/forms", () => ({
  ...jest.requireActual("src/use/forms"),
  useDirtyGuard: () => {},
  useFormState: () => ({
    dirty: mockRef(false),
    saved: mockRef(false),
    state: mockRef("idle"),
    queryLoading: mockRef(false),
    mutationLoading: mockRef(false),
    errorMessage: mockRef(""),
    setError: jest.fn(),
  }),
}))

installQuasarPlugin()
describe("StyleCriteriaForm", () => {
  const makeWrapper = (criteria) => {
    return mount(StyleCriteriaForm, {
      global: {
        mocks: {
          $t: (t) => t,
        },
        provide: {
          formState: useFormState(),
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
    expect(nameInput.classes("q-field--error")).toBe(true)
    expect(nameInput.text()).toContain("errors.required")

    //Name shorter than 20 chars
    await nameInput.findComponent({ name: "QInput" }).setValue("a".repeat(30))
    await form.trigger("submit")
    expect(nameInput.classes("q-field--error")).toBe(true)
    expect(nameInput.text()).toContain("errors.maxLength")

    //No more errors
    await nameInput.findComponent({ name: "QInput" }).setValue("Test Name")
    await form.trigger("submit")
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
    let okCallback
    const dialogReturn = {
      onOk: (okCb) => {
        okCallback = okCb
        return dialogReturn
      },
    }
    mockDialog.mockImplementation(() => dialogReturn)
    await wrapper.findComponent({ ref: "icon-button" }).trigger("click")
    okCallback("new-icon")
    await flushPromises()
    await wrapper.findComponent({ name: "QForm" }).trigger("submit")

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

    let okCallback
    const dialogReturn = {
      onOk: (okCb) => {
        okCallback = okCb
        return dialogReturn
      },
    }

    mockDialog.mockImplementation(() => dialogReturn)
    await wrapper.findComponent({ ref: "button_delete" }).trigger("click")
    expect(mockDialog).toHaveBeenCalled()
    expect(wrapper.emitted("delete")).toBeUndefined()
    okCallback()
    expect(wrapper.emitted("delete")).toHaveLength(1)
    expect(wrapper.emitted("delete")[0][0].id).toEqual("1")
  })
})

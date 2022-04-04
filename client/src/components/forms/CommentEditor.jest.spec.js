import CommentEditor from "./CommentEditor.vue"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { ref as mockRef } from "vue"

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
  }),
}))

installQuasarPlugin()
describe("CommentEditor", () => {
  const makeWrapper = (props = {}) => {
    return mount(CommentEditor, {
      global: {
        mocks: {
          $t: (t) => t,
        },
      },
      props: {
        ...props,
      },
    })
  }

  test("able to mount", () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
  })
})

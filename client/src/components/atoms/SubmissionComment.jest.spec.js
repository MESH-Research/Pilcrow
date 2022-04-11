import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import SubmissionComment from "./SubmissionComment.vue"

jest.mock("vue-i18n", () => ({
  useI18n: () => ({
    te: () => true,
    t: (t) => t,
  }),
}))
jest.mock("vue-router", () => ({
  useRouter: () => ({
    push: jest.fn(),
  }),
}))

installQuasarPlugin()
describe("SubmissionComment", () => {
  const wrapperFactory = () => {
    return {
      wrapper: mount(SubmissionComment, {
        global: {
          mocks: {
            $t: (token) => token,
          },
          stubs: ["router-link"],
        },
      }),
    }
  }

  beforeEach(() => {
    jest.clearAllMocks()
  })

  test("able to mount", () => {
    const { wrapper } = wrapperFactory()
    expect(wrapper).toBeTruthy()
  })
})

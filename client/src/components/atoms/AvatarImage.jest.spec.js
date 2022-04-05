import AvatarImage from "./AvatarImage.vue"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { mount } from "@vue/test-utils"

installQuasarPlugin()
describe("AvatarImage Component", () => {
  const factory = (email) => {
    return mount(AvatarImage, {
      props: {
        user: {
          email,
        },
      },
    })
  }

  it("returns a deterministic value", () => {
    const wrapper = factory("test@ccrproject.dev")
    expect(wrapper.vm.avatarSrc).toBe("avatar-yellow.png")
  })

  it("finds an element", () => {
    const wrapper = factory("test@ccrproject.dev")
    const element = wrapper.findComponent({ ref: "toggle_ref" })
    expect(element).toBeTruthy()
  })
})

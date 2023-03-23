import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import AvatarImage from "./AvatarImage.vue"

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
    const wrapper = factory("test@pilcrow.dev")
    expect(wrapper.vm.avatarSrc).toBe("avatar-magenta.png")
  })
})

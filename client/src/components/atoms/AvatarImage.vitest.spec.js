import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import AvatarImage from "./AvatarImage.vue"

import { describe, expect, it } from "vitest"

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
    const wrapper = factory("test@meshresearch.net")
    expect(wrapper.vm.avatarSrc).toBe("/avatar-purple.png")
  })
})

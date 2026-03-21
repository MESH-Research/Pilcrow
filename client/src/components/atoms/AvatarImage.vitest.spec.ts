import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import AvatarImage from "./AvatarImage.vue"

import { describe, expect, it } from "vitest"

installQuasarPlugin()
describe("AvatarImage Component", () => {
  const factory = (email: string) => {
    return mount(AvatarImage, {
      props: {
        user: {
          email
        }
      }
    })
  }

  it("returns a deterministic value", () => {
    const wrapper = factory("test@meshresearch.net")
    expect((wrapper.vm as any).avatarSrc).toBe("/avatar/avatar-purple.png")
  })
})

import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import AvatarImage from "./AvatarImage.vue"

import { describe, expect, it } from "vitest"

installQuasarPlugin()
describe("AvatarImage Component", () => {
  const factory = (avatar_color: string) => {
    return mount(AvatarImage, {
      props: {
        user: {
          avatar_color
        }
      }
    })
  }

  it("renders the color provided by the backend", () => {
    const wrapper = factory("purple")
    expect((wrapper.vm as any).avatarSrc).toBe("/avatar/avatar-purple.png")
  })
})

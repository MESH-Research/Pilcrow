import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import AvatarImage from "./AvatarImage.vue"
import type { avatarImageFragment } from "src/graphql/generated/graphql"

import { describe, expect, it } from "vitest"

installQuasarPlugin()
describe("AvatarImage Component", () => {
  const factory = (user: Partial<avatarImageFragment>) => {
    const fullUser: avatarImageFragment = {
      __typename: "User",
      id: "1",
      email: "user@example.com",
      avatar: null,
      ...user
    }
    return mount(AvatarImage, { props: { user: fullUser } })
  }

  it("renders an identicon SVG when the user has no uploaded avatar", () => {
    const wrapper = factory({ id: "42", email: "test@meshresearch.net" })
    const html = wrapper.html()
    expect(html).toContain("<svg")
    expect(wrapper.find(".identicon-wrap").exists()).toBe(true)
  })

  it("produces deterministic identicons for the same seed", () => {
    const a = factory({ id: "42" }).html()
    const b = factory({ id: "42" }).html()
    expect(a).toBe(b)
  })

  it("produces different identicons for different seeds", () => {
    const a = factory({ id: "42" }).html()
    const b = factory({ id: "43" }).html()
    expect(a).not.toBe(b)
  })

  it("renders the uploaded avatar when present", () => {
    const wrapper = factory({
      id: "1",
      avatar: {
        __typename: "Avatar",
        thumb_url: "https://example.com/avatar/thumb.png",
        medium_url: "https://example.com/avatar/medium.png",
        url: "https://example.com/avatar/original.png"
      }
    })
    expect(wrapper.html()).toContain("https://example.com/avatar/thumb.png")
    expect(wrapper.find(".identicon-wrap").exists()).toBe(false)
  })
})

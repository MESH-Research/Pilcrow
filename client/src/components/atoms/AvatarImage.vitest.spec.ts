import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import AvatarImage from "./AvatarImage.vue"
import type { avatarImageFragment } from "src/graphql/generated/graphql"

import { describe, expect, it } from "vitest"

installQuasarPlugin()
describe("AvatarImage Component", () => {
  const factory = (
    user: Partial<avatarImageFragment>,
    props: {
      hideStaged?: boolean
      variant?: "thumb" | "medium" | "original"
    } = {}
  ) => {
    const fullUser: avatarImageFragment = {
      __typename: "User",
      id: "1",
      email: "user@example.com",
      staged: null,
      avatar: null,
      ...user
    }
    return mount(AvatarImage, {
      props: { user: fullUser, ...props },
      global: {
        mocks: {
          $t: (key: string) => key
        }
      }
    })
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

  it("uses the original-size url when variant is 'original'", () => {
    const wrapper = factory(
      {
        id: "1",
        avatar: {
          __typename: "Avatar",
          thumb_url: "https://example.com/avatar/thumb.png",
          medium_url: "https://example.com/avatar/medium.png",
          url: "https://example.com/avatar/original.png"
        }
      },
      { variant: "original" }
    )
    expect(wrapper.html()).toContain("https://example.com/avatar/original.png")
  })

  it("uses the medium-size url when variant is 'medium'", () => {
    const wrapper = factory(
      {
        id: "1",
        avatar: {
          __typename: "Avatar",
          thumb_url: "https://example.com/avatar/thumb.png",
          medium_url: "https://example.com/avatar/medium.png",
          url: "https://example.com/avatar/original.png"
        }
      },
      { variant: "medium" }
    )
    expect(wrapper.html()).toContain("https://example.com/avatar/medium.png")
  })

  it("falls back to the original url when the requested conversion is missing", () => {
    // Conversions can lag behind the upload (queued). With only the original
    // present, every variant must still resolve to a usable image.
    const wrapper = factory(
      {
        id: "1",
        avatar: {
          __typename: "Avatar",
          thumb_url: "",
          medium_url: "",
          url: "https://example.com/avatar/original.png"
        }
      },
      { variant: "medium" }
    )
    expect(wrapper.html()).toContain("https://example.com/avatar/original.png")
    expect(wrapper.find(".identicon-wrap").exists()).toBe(false)
  })

  it("seeds the identicon from email when the user has no id", () => {
    // id is the preferred seed; with none, the email keeps the identicon stable.
    const a = factory({ id: "", email: "seed@example.com" }).html()
    const b = factory({ id: "", email: "seed@example.com" }).html()
    const c = factory({ id: "", email: "other@example.com" }).html()
    expect(a).toContain("<svg")
    expect(a).toBe(b)
    expect(a).not.toBe(c)
  })

  it("shows the staged corner marker when user.staged is true", () => {
    const wrapper = factory({ id: "7", staged: true })
    expect(wrapper.find(".avatar-staged-corner").exists()).toBe(true)
  })

  it("hides the staged corner marker when user.staged is null/false", () => {
    expect(
      factory({ id: "7", staged: null }).find(".avatar-staged-corner").exists()
    ).toBe(false)
    expect(
      factory({ id: "7", staged: false }).find(".avatar-staged-corner").exists()
    ).toBe(false)
  })

  it("suppresses the staged corner when hideStaged is set", () => {
    const wrapper = factory({ id: "7", staged: true }, { hideStaged: true })
    expect(wrapper.find(".avatar-staged-corner").exists()).toBe(false)
  })
})

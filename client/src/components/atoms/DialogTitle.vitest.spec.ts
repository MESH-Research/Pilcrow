import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { describe, expect, it } from "vitest"
import { QAvatar, QSeparator } from "quasar"
import DialogTitle from "./DialogTitle.vue"

installQuasarPlugin()

const factory = (props = {}) =>
  mount(DialogTitle, {
    props,
    slots: { default: "Heads up" }
  })

describe("DialogTitle", () => {
  it("renders the default slot content", () => {
    expect(factory().text()).toContain("Heads up")
  })

  it("omits the avatar when no icon is given", () => {
    expect(factory().findComponent(QAvatar).exists()).toBe(false)
  })

  it("renders the avatar with default colors when an icon is given", () => {
    const avatar = factory({ icon: "download" }).findComponent(QAvatar)
    expect(avatar.exists()).toBe(true)
    expect(avatar.props("icon")).toBe("download")
    expect(avatar.props("color")).toBe("white")
    expect(avatar.props("textColor")).toBe("accent")
  })

  it("honors avatar color overrides", () => {
    const avatar = factory({
      icon: "warning",
      avatarColor: "negative",
      avatarTextColor: "white"
    }).findComponent(QAvatar)
    expect(avatar.props("color")).toBe("negative")
    expect(avatar.props("textColor")).toBe("white")
  })

  it("shows a separator by default", () => {
    expect(factory().findComponent(QSeparator).exists()).toBe(true)
  })

  it("hides the separator when noSeparator is set", () => {
    expect(factory({ noSeparator: true }).findComponent(QSeparator).exists()).toBe(
      false
    )
  })
})

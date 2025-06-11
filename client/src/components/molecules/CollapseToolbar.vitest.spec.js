import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import CollapseToolbar from "./CollapseToolbar.vue"

import { describe, expect, test } from "vitest"

installQuasarPlugin()
describe("CollapseToolbar", () => {
  const makeWrapper = (slotContent, props) => {
    return mount(CollapseToolbar, {
      slots: {
        default: slotContent
      },
      props: {
        ...props
      }
    })
  }
  const defaultContent =
    "<div class='btn-1'>1BTN</div><div class='btn-2'>2BTN</div>"

  test("able to mount", () => {
    const wrapper = makeWrapper(defaultContent, {})
    expect(wrapper).toBeTruthy()
  })

  test("transparent wrapper when not collapsed", () => {
    const wrapper = makeWrapper(defaultContent, {})
    const items = wrapper.findAll(".btn-1, .btn-2")
    expect(items).toHaveLength(2)
    expect(items[0].classes()).toContain("btn-1")
  })

  test("dropdown is element is collapsed", async () => {
    const wrapper = makeWrapper(defaultContent, { collapse: true })

    const items = wrapper.findAll("q-item")
    expect(items).toHaveLength(2)
    expect(items[0].find("div.btn-1").exists()).toBeTruthy()
  })
})

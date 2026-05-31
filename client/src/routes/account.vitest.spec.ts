import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { computed, defineComponent, h, ref } from "vue"
import { describe, expect, it, beforeEach, vi } from "vitest"
import type { ChildRoute } from "src/use/navigation"

// Drive the layout from controllable composable stubs: childrenOf feeds
// the menu, currentUser gates the avatar block.
let children: ChildRoute[] = []
const currentUser = ref<{ id: string } | null>({ id: "1" })

vi.mock("src/use/navigation", () => ({
  useNavigation: () => ({ childrenOf: () => computed(() => children) })
}))
vi.mock("src/use/user", () => ({
  useCurrentUser: () => ({ currentUser })
}))

// Capture the items the layout maps onto the menu.
const CollapseMenuStub = defineComponent({
  name: "CollapseMenu",
  props: { items: { type: Array, default: () => [] } },
  setup: () => () => h("div", { class: "collapse-menu-stub" })
})

import AccountLayout from "./account.vue"

function childRoute(name: string, label: string, icon: string): ChildRoute {
  return {
    name,
    label,
    icon,
    url: { name },
    meta: {},
    component: undefined
  } as unknown as ChildRoute
}

installQuasarPlugin()

function factory() {
  return mount(AccountLayout, {
    global: {
      mocks: { $t: (t: string) => t },
      stubs: {
        AvatarBlock: true,
        CollapseMenu: CollapseMenuStub,
        "router-view": true
      }
    }
  })
}

describe("account layout", () => {
  beforeEach(() => {
    currentUser.value = { id: "1" }
    children = [
      childRoute("account:profile", "profile.page_title", "account_circle"),
      childRoute("account:labs", "labs.page_title", "o_science")
    ]
  })

  it("maps child routes onto the collapse menu, translating labels", () => {
    const wrapper = factory()
    const items = wrapper.findComponent(CollapseMenuStub).props("items")
    expect(items).toEqual([
      {
        icon: "account_circle",
        label: "profile.page_title",
        url: { name: "account:profile" }
      },
      {
        icon: "o_science",
        label: "labs.page_title",
        url: { name: "account:labs" }
      }
    ])
  })

  it("hides the avatar block until the current user resolves", () => {
    currentUser.value = null
    const wrapper = factory()
    expect(wrapper.findComponent(CollapseMenuStub).exists()).toBe(false)
  })
})

import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { computed, defineComponent, h, ref } from "vue"
import { describe, expect, it, beforeEach, vi } from "vitest"
import type { ChildRoute } from "src/use/navigation"

// Drive the layout from controllable composable stubs: childrenOf feeds
// the menu, currentUser gates the avatar block.
let children: ChildRoute[] = []
const currentUser = ref<{
  id: string
  feature_opt_ins?: string[]
} | null>({ id: "1" })

vi.mock("src/use/navigation", () => ({
  useNavigation: () => ({ childrenOf: () => computed(() => children) })
}))
// useCurrentUser is shared by the layout (avatar) and the real useFeatures
// composable, so feature_opt_ins on this stub drives the beta gate too.
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

function childRoute(
  name: string,
  label: string,
  icon: string,
  featureKey?: string
): ChildRoute {
  return {
    name,
    label,
    icon,
    url: { name },
    meta: featureKey ? { feature: { key: featureKey, private: true } } : {},
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

  it("hides a beta-gated child when the user has not opted in", () => {
    currentUser.value = { id: "1", feature_opt_ins: [] }
    children = [
      childRoute("account:profile", "profile.page_title", "account_circle"),
      childRoute(
        "account:record_of_review",
        "record_of_review.title",
        "history_edu",
        "record_of_review"
      )
    ]
    const wrapper = factory()
    const items = wrapper.findComponent(CollapseMenuStub).props("items")
    expect(items.map((i: { url: { name: string } }) => i.url.name)).toEqual([
      "account:profile"
    ])
  })

  it("shows a beta-gated child once the user opts into the feature", () => {
    currentUser.value = { id: "1", feature_opt_ins: ["record_of_review"] }
    children = [
      childRoute("account:profile", "profile.page_title", "account_circle"),
      childRoute(
        "account:record_of_review",
        "record_of_review.title",
        "history_edu",
        "record_of_review"
      )
    ]
    const wrapper = factory()
    const items = wrapper.findComponent(CollapseMenuStub).props("items")
    expect(items.map((i: { url: { name: string } }) => i.url.name)).toEqual([
      "account:profile",
      "account:record_of_review"
    ])
  })
})

import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { computed, defineComponent, h, ref } from "vue"
import { describe, expect, it, beforeEach, vi } from "vitest"
import type { ChildRoute } from "src/use/navigation"

// labs.vue normalizes a stray child-route name back to the index via a
// router.replace watcher; the page itself never routes to children.
const replace = vi.fn()
let routeName = "account:labs"
vi.mock("vue-router", () => ({
  useRoute: () => ({
    get name() {
      return routeName
    }
  }),
  useRouter: () => ({ replace })
}))

// Drive the page from controllable composable stubs so each test fixes
// the visible children + beta state.
const isBeta = ref(false)
let children: ChildRoute[] = []

vi.mock("src/use/navigation", () => ({
  useNavigation: () => ({ childrenOf: () => computed(() => children) })
}))
vi.mock("src/use/features", () => ({
  useFeatures: () => ({ isBeta })
}))

import LabsPage from "./labs.vue"

const stub = (text: string) =>
  defineComponent({ render: () => h("div", { class: "feature-stub" }, text) })

function child(
  key: string,
  isPrivate: boolean,
  order: number,
  label: string
): ChildRoute {
  return {
    name: `account:labs:${key}`,
    label,
    icon: undefined,
    url: { name: `account:labs:${key}` },
    meta: { feature: { key, private: isPrivate, order } },
    component: stub(label)
  } as unknown as ChildRoute
}

installQuasarPlugin()

function factory() {
  return mount(LabsPage, { global: { mocks: { $t: (t: string) => t } } })
}

describe("Labs page", () => {
  beforeEach(() => {
    isBeta.value = false
    routeName = "account:labs"
    children = [
      child("private_feature", true, 10, "Private Test"),
      child("public_thing", false, 20, "Public Thing")
    ]
    replace.mockReset()
  })

  it("hides private features from non-beta users", () => {
    const wrapper = factory()
    expect(wrapper.text()).toContain("Public Thing")
    expect(wrapper.text()).not.toContain("Private Test")
  })

  it("shows private features to beta users", () => {
    isBeta.value = true
    const wrapper = factory()
    expect(wrapper.text()).toContain("Private Test")
    expect(wrapper.text()).toContain("Public Thing")
  })

  it("shows the empty banner when no features are visible", () => {
    children = [child("private_feature", true, 10, "Private Test")]
    const wrapper = factory()
    expect(wrapper.find('[data-cy="no_labs_access"]').exists()).toBe(true)
  })

  it("redirects back to the index when a child route is hit directly", () => {
    routeName = "account:labs:private_feature"
    factory()
    expect(replace).toHaveBeenCalledWith({ name: "account:labs" })
  })

  it("does not redirect when already on the index route", () => {
    factory()
    expect(replace).not.toHaveBeenCalled()
  })
})

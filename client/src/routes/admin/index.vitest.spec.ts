import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import { computed, ref } from "vue"
import type { ChildRoute } from "src/use/navigation"
import AdminDashboard from "./index.vue"
import { beforeEach, describe, expect, it, vi } from "vitest"

const mockPush = vi.fn()
vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: mockPush
  }),
  useRoute: () => ({
    query: {}
  })
}))

// Cards are built from the admin layout's children via childrenOf; drive
// them from a controllable stub so the test fixes which pages appear.
const pendingCount = ref(0)
let children: ChildRoute[] = []
vi.mock("src/use/navigation", () => ({
  useNavigation: () => ({ childrenOf: () => computed(() => children) })
}))
vi.mock("src/use/avatarReports", () => ({
  useAvatarReportsPendingCount: () => ({
    count: pendingCount,
    canModerate: ref(false),
    refetch: vi.fn()
  })
}))

installQuasarPlugin()

function navChild(
  name: string,
  label: string,
  icon: string,
  description: string
): ChildRoute {
  return {
    name,
    label,
    icon,
    url: { name },
    meta: { navigation: { label, icon, description } },
    component: undefined
  } as unknown as ChildRoute
}

describe("AdminDashboard", () => {
  const makeWrapper = () => mount(AdminDashboard)

  beforeEach(() => {
    mockPush.mockClear()
    pendingCount.value = 0
    children = [
      navChild(
        "admin:users-section",
        "header.user_list",
        "groups",
        "users_desc"
      ),
      navChild(
        "admin:publication:index",
        "header.publications",
        "collections_bookmark",
        "pubs_desc"
      ),
      navChild(
        "admin:beta-users",
        "admin.beta_users.title",
        "science",
        "beta_desc"
      ),
      navChild(
        "admin:avatar_reports",
        "admin_avatar_reports.page_title",
        "flag",
        "avatar_desc"
      )
    ]
  })

  it("mounts without errors", () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  it("renders one card per navigation child", () => {
    const wrapper = makeWrapper()
    const cards = wrapper.findAll(".admin-card")
    expect(cards).toHaveLength(4)
  })

  it("ignores siblings without navigation meta", () => {
    children = [
      ...children,
      {
        name: "admin:user:id",
        label: "admin:user:id",
        icon: undefined,
        url: { name: "admin:user:id" },
        meta: {},
        component: undefined
      } as unknown as ChildRoute
    ]
    const wrapper = makeWrapper()
    expect(wrapper.findAll(".admin-card")).toHaveLength(4)
  })

  it("navigates to users page on card click", async () => {
    const wrapper = makeWrapper()
    const cards = wrapper.findAll(".admin-card")
    await cards[0].trigger("click")
    expect(mockPush).toHaveBeenCalledWith({ name: "admin:users-section" })
  })

  it("navigates to publications page on card click", async () => {
    const wrapper = makeWrapper()
    const cards = wrapper.findAll(".admin-card")
    await cards[1].trigger("click")
    expect(mockPush).toHaveBeenCalledWith({ name: "admin:publication:index" })
  })

  it("navigates to beta-users page on card click", async () => {
    const wrapper = makeWrapper()
    const cards = wrapper.findAll(".admin-card")
    await cards[2].trigger("click")
    expect(mockPush).toHaveBeenCalledWith({ name: "admin:beta-users" })
  })

  it("navigates to avatar-reports page on card click", async () => {
    const wrapper = makeWrapper()
    const cards = wrapper.findAll(".admin-card")
    await cards[3].trigger("click")
    expect(mockPush).toHaveBeenCalledWith({ name: "admin:avatar_reports" })
  })

  it("shows a pending badge only on the avatar-reports card", () => {
    pendingCount.value = 3
    const wrapper = makeWrapper()
    const badge = wrapper.find("[data-cy='admin_card_avatar_reports_badge']")
    expect(badge.exists()).toBe(true)
    expect(badge.text()).toContain("3")
    expect(wrapper.findAll(".q-badge")).toHaveLength(1)
  })
})

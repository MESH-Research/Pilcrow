import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import AdminDashboard from "./AdminDashboard.vue"
import { describe, expect, it, vi } from "vitest"
import { computed } from "vue"

const mockPush = vi.fn()
vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: mockPush
  }),
  useRoute: () => ({
    query: {}
  })
}))

vi.mock("src/use/avatarReports", () => ({
  useAvatarReportsPendingCount: () => ({
    count: computed(() => 0),
    canModerate: computed(() => true),
    refetch: vi.fn()
  })
}))

installQuasarPlugin()

describe("AdminDashboard", () => {
  const makeWrapper = () => mount(AdminDashboard)

  it("mounts without errors", () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  it("renders links to users, publications, and avatar reports pages", () => {
    const wrapper = makeWrapper()
    const cards = wrapper.findAll(".admin-card")
    expect(cards).toHaveLength(3)
  })

  it("navigates to users page on card click", async () => {
    const wrapper = makeWrapper()
    const cards = wrapper.findAll(".admin-card")
    await cards[0].trigger("click")
    expect(mockPush).toHaveBeenCalledWith("/admin/users")
  })

  it("navigates to publications page on card click", async () => {
    const wrapper = makeWrapper()
    const cards = wrapper.findAll(".admin-card")
    await cards[1].trigger("click")
    expect(mockPush).toHaveBeenCalledWith({ name: "admin:publication:index" })
  })

  it("navigates to avatar reports page on card click", async () => {
    const wrapper = makeWrapper()
    const cards = wrapper.findAll(".admin-card")
    await cards[2].trigger("click")
    expect(mockPush).toHaveBeenCalledWith({ name: "admin:avatar_reports" })
  })
})

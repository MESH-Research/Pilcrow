import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount } from "@vue/test-utils"
import AdminDashboard from "./index.vue"
import { describe, expect, it, vi } from "vitest"

const mockPush = vi.fn()
vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: mockPush
  }),
  useRoute: () => ({
    query: {}
  })
}))

installQuasarPlugin()

describe("AdminDashboard", () => {
  const makeWrapper = () => mount(AdminDashboard)

  it("mounts without errors", () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
  })

  it("renders links to users, publications, and beta-users pages", () => {
    const wrapper = makeWrapper()
    const cards = wrapper.findAll(".admin-card")
    expect(cards).toHaveLength(3)
  })

  it("navigates to users page on card click", async () => {
    const wrapper = makeWrapper()
    const cards = wrapper.findAll(".admin-card")
    await cards[0].trigger("click")
    expect(mockPush).toHaveBeenCalledWith({ name: "admin:users" })
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
})

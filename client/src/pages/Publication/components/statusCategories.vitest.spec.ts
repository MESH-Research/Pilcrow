import { describe, expect, it } from "vitest"
import { statusCategories, statusStyleMap } from "./statusCategories"

describe("statusCategories", () => {
  it("defines three categories", () => {
    expect(statusCategories).toHaveLength(3)
    expect(statusCategories.map((c) => c.key)).toEqual([
      "needs_action",
      "in_progress",
      "completed"
    ])
  })

  it("does not include DRAFT in any category", () => {
    const allStatuses = statusCategories.flatMap((c) => c.statuses)
    expect(allStatuses).not.toContain("DRAFT")
  })

  it("has no duplicate statuses across categories", () => {
    const allStatuses = statusCategories.flatMap((c) => c.statuses)
    const unique = new Set(allStatuses)
    expect(unique.size).toBe(allStatuses.length)
  })

  it("every category has required style fields", () => {
    for (const cat of statusCategories) {
      expect(cat.color).toBeTruthy()
      expect(cat.textClass).toBeTruthy()
      expect(cat.icon).toBeTruthy()
      expect(typeof cat.pattern).toBe("string")
      expect(cat.statuses.length).toBeGreaterThan(0)
    }
  })
})

describe("statusStyleMap", () => {
  it("maps every categorized status to a style", () => {
    const allStatuses = statusCategories.flatMap((c) => c.statuses)
    for (const status of allStatuses) {
      expect(statusStyleMap).toHaveProperty(status)
      const style = statusStyleMap[status]
      expect(style.color).toBeTruthy()
      expect(style.textClass).toBeTruthy()
      expect(style.icon).toBeTruthy()
    }
  })

  it("does not contain DRAFT", () => {
    expect(statusStyleMap).not.toHaveProperty("DRAFT")
  })

  it("maps statuses to their parent category style", () => {
    for (const cat of statusCategories) {
      for (const status of cat.statuses) {
        expect(statusStyleMap[status].color).toBe(cat.color)
        expect(statusStyleMap[status].icon).toBe(cat.icon)
      }
    }
  })
})

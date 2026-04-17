import { describe, expect, it, vi } from "vitest"
import { defaultStatuses } from "./DashboardStatusFilter.vue"

describe("DashboardStatusFilter exports", () => {
  describe("defaultStatuses", () => {
    it("is an array of strings", () => {
      expect(Array.isArray(defaultStatuses)).toBe(true)
      for (const s of defaultStatuses) {
        expect(typeof s).toBe("string")
      }
    })

    it("excludes DRAFT", () => {
      expect(defaultStatuses).not.toContain("DRAFT")
    })

    it("excludes EXPIRED", () => {
      expect(defaultStatuses).not.toContain("EXPIRED")
    })

    it("excludes ARCHIVED", () => {
      expect(defaultStatuses).not.toContain("ARCHIVED")
    })

    it("excludes DELETED", () => {
      expect(defaultStatuses).not.toContain("DELETED")
    })

    it("includes active workflow statuses", () => {
      const expected = [
        "INITIALLY_SUBMITTED",
        "RESUBMISSION_REQUESTED",
        "RESUBMITTED",
        "AWAITING_REVIEW",
        "UNDER_REVIEW",
        "AWAITING_DECISION",
        "REVISION_REQUESTED",
        "REJECTED",
        "ACCEPTED_AS_FINAL"
      ]
      for (const s of expected) {
        expect(defaultStatuses).toContain(s)
      }
    })

    it("has exactly 9 default statuses", () => {
      expect(defaultStatuses).toHaveLength(9)
    })
  })
})

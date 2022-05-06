/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"
import { a11yLogViolations } from '../support/helpers'

describe("Submissions Details", () => {
  it("should assert the Submission Details page is accessible", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.injectAxe()
    cy.dataCy("list_assigned_submitters")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow assignments of reviewers by application administrators", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.dataCy("input_review_assignee").type("applicationAd")
    cy.dataCy("result_review_assignee").click()
    cy.dataCy("review_assignee_selected").contains("applicationAdminUser")

    cy.dataCy("button_assign_reviewer").click()
    cy.dataCy("list_assigned_reviewers").contains("Application Administrator")
    cy.injectAxe()
    cy.dataCy("submission_details_notify")

    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow assignments of reviewers by review coordinators", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.dataCy("input_review_assignee").type("applicationAd")
    cy.dataCy("result_review_assignee").click()
    cy.dataCy("review_assignee_selected").contains("applicationAdminUser")
    cy.dataCy("button_assign_reviewer").click()
    cy.dataCy("list_assigned_reviewers").contains("Application Administrator")
    cy.injectAxe()
    cy.dataCy("submission_details_notify")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow assignments of review coordinators by application administrators", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.dataCy("input_review_coordinator_assignee").type("applicationAd")
    cy.dataCy("result_review_coordinator_assignee").click()
    cy.dataCy("review_coordinator_assignee_selected").contains(
      "applicationAdminUser"
    )
    cy.dataCy("button_assign_review_coordinator").click()
    cy.dataCy("list_assigned_review_coordinators").contains(
      "Application Administrator"
    )
    cy.injectAxe()
    cy.dataCy("submission_details_notify")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should disallow assignments of duplicate reviewers", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.dataCy("input_review_assignee").type("applicationAd")
    cy.dataCy("result_review_assignee").click()
    cy.dataCy("button_assign_reviewer").click()
    cy.dataCy("button_dismiss_notify").click()
    cy.dataCy("input_review_assignee").type("applicationAd")
    cy.dataCy("result_review_assignee").click()
    cy.dataCy("button_assign_reviewer").click()
    cy.injectAxe()
    cy.dataCy("submission_details_notify")
      .should("be.visible")
      .should("have.class", "bg-negative")
    cy.checkA11y(null, null, a11yLogViolations)
  })
})

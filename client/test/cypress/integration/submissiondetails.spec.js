/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"

describe("Submissions Details", () => {
  it("should assert the Submission Details page is accessible", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.injectAxe()
    cy.dataCy("list_assigned_submitters")
    cy.checkA11y()
  })

  it("should allow assignments of reviewers by application administrators", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.get("#input_review_assignee").type("applicationAd{backspace}{backspace}")
    cy.dataCy("result_review_assignee").click()
    cy.dataCy("review_assignee_selected").contains("applicationAdminUser")
    cy.get("#input_review_assignee").type(
      "test{backspace}{backspace}{backspace}{backspace}"
    )
    cy.dataCy("button_assign_reviewer").click()
    cy.dataCy("list_assigned_reviewers").contains("Application Administrator")
    cy.injectAxe()
    cy.dataCy("submission_details_notify")
    cy.get("#input_review_assignee").type(
      "typing to prevent a false positive a11y violation before submission_details_notify fully fades in"
    )
    cy.checkA11y(null, {
      rules: {
        "nested-interactive": { enabled: false },
      },
    })
  })

  it("should allow assignments of reviewers by review coordinators", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.get("#input_review_assignee").type("applicationAd{backspace}{backspace}")
    cy.dataCy("result_review_assignee").click()
    cy.dataCy("review_assignee_selected").contains("applicationAdminUser")
    cy.get("#input_review_assignee").type(
      "test{backspace}{backspace}{backspace}{backspace}"
    )
    cy.dataCy("button_assign_reviewer").click()
    cy.dataCy("list_assigned_reviewers").contains("Application Administrator")
    cy.injectAxe()
    cy.dataCy("submission_details_notify")
    // Type characters to delay the a11y checker and prevent a false positive
    // a11y violation before submission_details_notify fully fades in
    cy.get("#input_review_assignee").type("allow notify to fully fade in")
    cy.checkA11y(null, {
      rules: {
        "nested-interactive": { enabled: false },
      },
    })
  })

  it("should allow assignments of review coordinators by application administrators", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.get("#input_review_coordinator_assignee").type(
      "applicationAd{backspace}{backspace}"
    )
    cy.dataCy("result_review_coordinator_assignee").click()
    cy.dataCy("review_coordinator_assignee_selected").contains(
      "applicationAdminUser"
    )
    cy.get("#input_review_coordinator_assignee").type(
      "test{backspace}{backspace}{backspace}{backspace}"
    )
    cy.dataCy("button_assign_review_coordinator").click()
    cy.dataCy("list_assigned_review_coordinators").contains(
      "Application Administrator"
    )
    cy.injectAxe()
    cy.dataCy("submission_details_notify")
    // Type characters to delay the a11y checker and prevent a false positive
    // a11y violation before submission_details_notify fully fades in
    cy.get("#input_review_coordinator_assignee").type(
      "allow notify to fully fade in"
    )
    cy.checkA11y(null, {
      rules: {
        "nested-interactive": { enabled: false },
      },
    })
  })

  it("should disallow assignments of duplicate reviewers", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.get("#input_review_assignee").type("applicationAd{backspace}{backspace}")
    cy.dataCy("result_review_assignee").click()
    cy.dataCy("button_assign_reviewer").click()
    cy.dataCy("button_dismiss_notify").click()
    cy.get("#input_review_assignee").type("applicationAd{backspace}{backspace}")
    cy.dataCy("result_review_assignee").click()
    cy.dataCy("button_assign_reviewer").click()
    cy.injectAxe()
    cy.dataCy("submission_details_notify")
      .should("be.visible")
      .should("have.class", "bg-negative")
    cy.get("#input_review_assignee").type(
      "typing to prevent a false positive a11y violation before submission_details_notify fully fades in"
    )
    cy.checkA11y(null, {
      rules: {
        "nested-interactive": { enabled: false },
      },
    })
  })
})

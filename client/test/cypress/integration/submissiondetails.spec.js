/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"

describe("Submissions Details", () => {
  it("should assert the Submission Details page is accessible", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.injectAxe()
    cy.dataCy("list_assigned_reviewers")
    cy.checkA11y()
  })

  it("should allow assignments of reviewers", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.get("#review_assignee_input").type("applicationAdminUser")
    cy.dataCy("review_assignee_result").click()
    cy.dataCy("review_assignee_selected").contains("applicationAdminUser")
    cy.get("#review_assignee_input").type(
      "test{backspace}{backspace}{backspace}{backspace}"
    )
    cy.dataCy("button_assign_reviewer").click()
    cy.dataCy("list_assigned_reviewers").contains("Application Admin User")
    cy.injectAxe()
    cy.dataCy("submission_details_notify")
    cy.checkA11y(null, {
      rules: {
        "nested-interactive": { enabled: false },
        "color-contrast": { enabled: false }, // TODO: re-enable when axe DevTools implements v4.3.3 of axe-core
      },
    })
  })
})

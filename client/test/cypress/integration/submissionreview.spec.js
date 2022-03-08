/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"

describe("Submissions Review", () => {
  it("should assert the Submission Review page is accessible", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/review/100")
    cy.injectAxe()
    cy.dataCy("submission_review_layout")
    cy.checkA11y()
  })

  it("should assert the Submission Review page can be accessed from the dashboard", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("/dashboard")
    cy.dataCy("sidebar_toggle").click()
    cy.dataCy("submissions_link").click()
    cy.dataCy("submission_link").contains("CCR Test Submission 1").click()
    cy.dataCy("submission_review_btn").click()
    cy.dataCy("submission_review_layout")
  })
})

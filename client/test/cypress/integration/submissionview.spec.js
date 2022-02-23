/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"

describe("Submissions View", () => {
  it("should assert the Submission View page is accessible", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/view/100")
    cy.injectAxe()
    cy.dataCy("submission_view_layout")
    cy.checkA11y()
  })

  it("should assert the Submission View page can be accessed from the dashboard", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("/dashboard")
    cy.dataCy("sidebar_toggle").click()
    cy.dataCy("submissions_link").click()
    cy.dataCy("submission_link").contains("CCR Test Submission 1").click()
    cy.dataCy("submission_view_btn").click()
    cy.dataCy("submission_view_layout")
  })
})

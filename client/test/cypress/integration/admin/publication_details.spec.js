/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />

import "cypress-axe"

describe("Publication Details", () => {
  beforeEach(() => {
    cy.task("resetDb")
  })

  it("restricts access based on role", () => {
    cy.login({ email: "regularuser@ccrproject.dev" })
    cy.visit("/publication/1")
    cy.url().should("include", "/error403")
  })

  it("allows access based on role", () => {
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("/publication/1")
    cy.url().should("not.include", "/error403")
  })

  it("should assert the Publication Details page is accessible", () => {
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("/publication/1")
    cy.injectAxe()
    cy.dataCy("publication_details_heading")
    cy.checkA11y()
  })

  it("should allow assignments of editors and reject assignments of duplicate editors", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("/publication/1")
    // Initial Assignment
    cy.get("#input_editor_assignee").type("applicationAd{backspace}{backspace}")
    cy.dataCy("result_editor_assignee").click()
    cy.dataCy("editor_assignee_selected").contains("applicationAdminUser")
    cy.dataCy("button_assign_editor").click()
    cy.dataCy("button_dismiss_notify").click()
    // Duplicate Assignment
    cy.get("#input_editor_assignee").type("applicationAd{backspace}{backspace}")
    cy.dataCy("result_editor_assignee").click()
    cy.dataCy("button_assign_editor").click()
    // Type characters to delay the a11y checker and prevent a false positive
    // a11y violation before publication_details_notify fully fades in
    cy.get("#input_editor_assignee").type("allow notify to fully fade in")
    cy.injectAxe()
    cy.dataCy("publication_details_notify")
      .should("be.visible")
      .should("have.class", "bg-negative")

    cy.checkA11y(null, {
      rules: {
        "nested-interactive": { enabled: false },
      },
    })
  })
})

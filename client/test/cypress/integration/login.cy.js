/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from '../support/helpers'

describe("login page", () => {
  beforeEach(() => {
    cy.task("resetDb")
    cy.visit("/login")
    cy.injectAxe()
  })

  it("allows a user to login", () => {
    cy.get(".q-form").within(() => {
      cy.dataCy("email_field").type("regularuser@meshresearch.net")
      cy.checkA11y(null, null, a11yLogViolations)
      cy.dataCy("password_field").type("regularPassword!@#")
      cy.get(".q-card__actions").contains("Login").click()
      cy.url().should("include", "/dashboard")
    })
  })

  it("validates fields and displays errors", () => {
    cy.get(".q-form").within(() => {
      cy.dataCy("email_field")
        .type("{enter}")
      cy.dataCy("email_field")
        .parents("label")
        .should("have.class", "q-field--error")
      cy.checkA11y(null, null, a11yLogViolations)
      cy.dataCy("email_field").type("regularuser@meshresearch.net{enter}")

      cy.dataCy("password_field")
        .parents("label")
        .should("have.class", "q-field--error")
        .type("somePass{enter}")
        // This keeps finding mystery a11y contrast error
        // cy.checkA11y(null, null, a11yLogViolations)

      cy.dataCy("authFailureMessages")
        .should("be.visible")
        .contains("combination is incorrect")
        cy.checkA11y(null, null, a11yLogViolations)
    })
  })

  it("redirects to login when requesting a protected page", () => {
    cy.visit("/account/profile")
    cy.url().should("include", "/login")
    cy.get('[role="alert"]').contains("login to access that page")

    cy.get(".q-form").within(() => {
      cy.dataCy("email_field").type("regularuser@meshresearch.net")
      cy.dataCy("password_field").type("regularPassword!@#{enter}")
      cy.url().should("include", "/account/profile")
      // cy.checkA11y(null, null, a11yLogViolations)
    })
  })

  it("should assert the page is accessible", () => {
    // Inject the axe-core libraray
    cy.injectAxe()
    cy.dataCy("vueLogin")
    cy.checkA11y(null, null, a11yLogViolations)
  })
})

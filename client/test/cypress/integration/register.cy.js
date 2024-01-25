/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from '../support/helpers'

describe("Register", () => {
  beforeEach(() => {
    cy.visit("/register")
  })

  it("validates fields", () => {
    cy.task("resetDb")
    cy.injectAxe()
    cy.get(".q-form").within(() => {
      //Email is required
      cy.dataCy("email_field")
        .type("{enter}")
      cy.dataCy("email_field")
        .parents("label")
        .should("have.class", "q-field--error")
      cy.checkA11y(null, null, a11yLogViolations)

      //Email must be valid
      cy.dataCy("email_field")
        .clear()
      cy.dataCy("email_field")
        .type("pilcrowproject{enter}")
      cy.dataCy("email_field")
        .parents("label")
        .should("have.class", "q-field--error")
        .contains("a valid email")
      cy.checkA11y(null, null, a11yLogViolations)

      //Email success
      cy.dataCy("email_field")
        .type("@meshresearch.net{enter}")
      cy.dataCy("email_field")
        .parents("label")
        .should("not.have.class", "q-field--error")
      cy.checkA11y(null, null, a11yLogViolations)

      //Username is required
      cy.dataCy("username_field")
        .parents("label")
        .should("have.class", "q-field--error")
        .contains("is required")
      cy.checkA11y(null, null, a11yLogViolations)

      //Username success
      cy.dataCy("username_field")
        .type("newUser{enter}")
      cy.dataCy("username_field")
        .parents("label")
        .should("not.have.class", "q-field--error")
      cy.checkA11y(null, null, a11yLogViolations)

      //Password is required
      cy.dataCy("password_field")
        .parents("label")
        .should("have.class", "q-field--error")
        .contains("is required")
      cy.checkA11y(null, null, a11yLogViolations)

      //Password must be complex
      cy.dataCy("password_field")
        .type("password")
      cy.dataCy("password_field")
        .parents("label")
        .should("have.class", "q-field--error")
        .contains("be more complex")
      cy.checkA11y(null, null, a11yLogViolations)

      //Password success
      cy.dataCy("password_field")
        .type("!@#$#@password")
      cy.dataCy("password_field")
        .parents("label")
        .should("not.have.class", "q-field--error")
      cy.checkA11y(null, null, a11yLogViolations)

      //Username must be unique
      cy.dataCy("username_field")
        .clear()
      cy.dataCy("username_field")
        .type("regularUser{enter}")
      cy.dataCy("username_field")
        .parents("label")
        .should("have.class", "q-field--error")
        .contains("is not available")
      cy.dataCy("username_field").type("brandnewusername")
      cy.checkA11y(null, null, a11yLogViolations)

      //Email must be unique
      cy.dataCy("email_field")
        .clear()
      cy.dataCy("email_field")
        .type("regularuser@meshresearch.net{enter}")
      cy.dataCy("email_field")
        .parents("label")
        .should("have.class", "q-field--error")
        .contains("already registered")
      cy.checkA11y(null, null, a11yLogViolations)

      cy.dataCy("email_field")
        .clear()
      cy.dataCy("email_field")
        .type("newvalidemail@meshresearch.net")

      cy.get('[type="submit"]').click()
      cy.url().should("include", "/dashboard")
      cy.checkA11y(null, null, a11yLogViolations)
    })
  })

  it("submits with enter key", () => {
    cy.task("resetDb")
    cy.injectAxe()
    cy.get(".q-form").within(() => {
      cy.dataCy("username_field").type("newUserName")
      cy.dataCy("email_field").type("newEmail@meshresearch.net")
      cy.dataCy("password_field").type("password_field!@#12{enter}")
    })

    cy.url().should("include", "/dashboard")
  })

  it("should assert the My Dashboard page is accessible", () => {
    // Inject the axe-core libraray
    cy.injectAxe()
    cy.dataCy("vueRegister")
    cy.checkA11y(null, {
      rules: {
        "autocomplete-valid": { enabled: false },
      },
    }, a11yLogViolations)
  })
})

/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from '../support/helpers'

describe("Register", () => {
  beforeEach(() => {
    cy.visit("/register")
  })

  it("validates fields", () => {
    cy.task("resetDb")
    cy.get(".q-form").within(() => {
      //Email is required
      cy.dataCy("email_field")
        .type("{enter}")
        .parents("label")
        .should("have.class", "q-field--error")

      //Email must be valid
      cy.dataCy("email_field")
        .clear()
        .type("pilcrowproject{enter}")
        .parents("label")
        .should("have.class", "q-field--error")
        .contains("a valid email")

      //Email success
      cy.dataCy("email_field")
        .type("@meshresearch.net{enter}")
        .parents("label")
        .should("not.have.class", "q-field--error")

      //Username is required
      cy.dataCy("username_field")
        .parents("label")
        .should("have.class", "q-field--error")
        .contains("is required")

      //Username success
      cy.dataCy("username_field")
        .type("newUser{enter}")
        .parents("label")
        .should("not.have.class", "q-field--error")

      //Password is required
      cy.dataCy("password_field")
        .parents("label")
        .should("have.class", "q-field--error")
        .contains("is required")

      //Password must be complex
      cy.dataCy("password_field")
        .type("password")
        .parents("label")
        .should("have.class", "q-field--error")
        .contains("be more complex")

      //Password success
      cy.dataCy("password_field")
        .type("!@#$#@password")
        .parents("label")
        .should("not.have.class", "q-field--error")

      //Username must be unique
      cy.dataCy("username_field")
        .clear()
        .type("regularUser{enter}")
        .parents("label")
        .should("have.class", "q-field--error")
        .contains("is not available")
      cy.dataCy("username_field").type("brandnewusername")

      //Email must be unique
      cy.dataCy("email_field")
        .clear()
        .type("regularuser@meshresearch.net{enter}")
        .parents("label")
        .should("have.class", "q-field--error")
        .contains("already registered")

      cy.dataCy("email_field").clear().type("newvalidemail@meshresearch.net")

      cy.get('[type="submit"]').click()
      cy.url().should("include", "/dashboard")
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

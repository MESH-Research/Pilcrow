/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from "../support/helpers"

describe("Settings page", () => {
  beforeEach(() => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@pilcrow.dev" })
    cy.visit("/account/settings")
  })

  it("can update the email field", () => {
    cy.injectAxe()
    cy.dataCy("update_user_email").clear().type("updateduser@pilcrow.dev")
    cy.dataCy("button_save").click()
    cy.dataCy("update_user_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("can update the password field", () => {
    cy.injectAxe()
    cy.dataCy("update_user_password").clear().type("XMYeygtC7TuxgER4")
    cy.dataCy("button_save").click()
    cy.dataCy("update_user_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")
    cy.checkA11y(null, null, a11yLogViolations)
  })
})

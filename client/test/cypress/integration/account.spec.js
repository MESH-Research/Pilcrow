/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"
import { a11yLogViolations } from '../support/helpers'

describe("Account", () => {
  beforeEach(() => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@ccrproject.dev" })
    cy.visit("/account/profile")
  })

  it("can update the name field", () => {
    cy.dataCy("update_user_name").clear().type("Updated User")
    cy.dataCy("button_save").click()
    cy.dataCy("avatar_name").contains("Updated User")
  })

  it("can update the username field", () => {
    cy.dataCy("update_user_username").clear().type("updatedUser")
    cy.dataCy("button_save").click()
    cy.dataCy("avatar_username").contains("updatedUser")
  })

  it("can update the password field", () => {
    cy.dataCy("update_user_password").clear().type("XMYeygtC7TuxgER4")
    cy.dataCy("button_save").click()
    cy.dataCy("update_user_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")
    // Inject the axe-core libraray
  })

  it("should assert the page is accessible", () => {
    cy.injectAxe()
    //Wait for the page to be loaded.
    cy.dataCy("vueAccount")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  // TODO: Uncomment once email updates work again
  // it('can update the email field', () => {
  //   cy.dataCy('update_user_email').clear().type('updateduser@ccrproject.dev');
  //   cy.dataCy('update_user_button_save').click();
  //   cy.dataCy('update_user_notify').should('be.visible').should('have.class','bg-positive');
  // });
})

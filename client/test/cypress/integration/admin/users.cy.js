/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />
// Use `cy.dataCy` custom command for more robust tests
// See https://docs.cypress.io/guides/references/best-practices.html#Selecting-Elements

import { a11yLogViolations } from '../../support/helpers'

describe("Admin Users Index", () => {
  beforeEach(() => {
    cy.task("resetDb")
  })

  it("restricts access based on role", () => {
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("/admin/users")
    cy.url().should("include", "/error403")
  })

  it("allows access based on role", () => {
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("/admin/users")
    cy.url().should("not.include", "/error403")
  })

  it("should assert the page is accessible", () => {
    // Inject the axe-core libraray
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("/admin/users")
    cy.injectAxe()
    cy.dataCy("userListBasicItem")
    cy.checkA11y(null, null, a11yLogViolations)
  })
})

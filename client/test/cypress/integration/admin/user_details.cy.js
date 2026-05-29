/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />
// Use `cy.dataCy` custom command for more robust tests
// See https://docs.cypress.io/guides/references/best-practices.html#Selecting-Elements

import { a11yLogViolations } from "../../support/helpers"

describe("Admin User Details", () => {
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

  it("should assert the User Details page of an admin is accessible", () => {
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("/admin/user/2")
    cy.get(".q-tabs").should("be.visible")
    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should assert the User Details page of an non-admin is accessible", () => {
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("/admin/user/1")
    cy.get(".q-tabs").should("be.visible")
    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })
})

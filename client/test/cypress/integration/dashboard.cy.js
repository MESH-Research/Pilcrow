/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from '../support/helpers'

describe("Dashboard", () => {
  beforeEach(() => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
  })
  it("should assert the page is accessible", () => {
    cy.visit("/dashboard")
    cy.injectAxe()
    //Wait for the page to be loaded.
    cy.dataCy("vueDashboard")
    cy.checkA11y(null, null, a11yLogViolations)
  })
})

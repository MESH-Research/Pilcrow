/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from '../support/helpers'
import "cypress-file-upload"

describe("Submissions Page", () => {
  beforeEach(() => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@pilcrow.dev" })
  })
  it("should assert the page is accessible", () => {
    cy.visit("/submissions")
    cy.injectAxe()
    cy.dataCy("submissions_title")
    cy.checkA11y(null, null, a11yLogViolations)
  })
})

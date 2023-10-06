/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from '../support/helpers'

describe("Publications", () => {

  it("excludes the publications from the list when they are not publicly visible", () => {
    // TODO: test needs to be rewritten when the functionality to check the visibility of a publication is implemented
    // cy.login({ email: "regularuser@meshresearch.net" })
    // cy.visit("/publications")
    // cy.injectAxe()
    // cy.dataCy("publications_list").should("be.empty")
    // cy.checkA11y(null, null, a11yLogViolations)
  })
})

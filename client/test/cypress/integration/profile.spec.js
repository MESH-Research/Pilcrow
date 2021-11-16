/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"

describe("Profile", () => {
  beforeEach(() => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@ccrproject.dev" })
    cy.visit("/account/metadata")
  })

  it("can update the professional title", () => {
    cy.dataCy("professional_title").type("Updated Professional Title")
    cy.dataCy("button_save").click()
    cy.dataCy("button_saved").contains("Saved")
    cy.reload()
    cy.dataCy("professional_title").should(
      "have.value",
      "Updated Professional Title"
    )
  })
})

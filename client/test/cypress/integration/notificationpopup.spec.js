/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"
import "cypress-file-upload"

describe("Notification Popup", () => {
  it("allows individual notifications to be marked as read", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@ccrproject.dev" })
  })

  it("allows multiple notifications to be marked as read", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@ccrproject.dev" })
  })
})

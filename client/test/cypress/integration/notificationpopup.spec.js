/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"
import "cypress-file-upload"

describe("Notification Popup", () => {
  it("allows an individual notification to be marked as read", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@ccrproject.dev" })
    cy.visit("/dashboard")
    cy.dataCy("dropdown_notificiations").click()
    cy.dataCy("notification_list_item").eq(0).click()
    cy.dataCy("notification_list_item").eq(0).should("not.have.class", "unread")
    cy.dataCy("notification_list_item").eq(1).should("have.class", "read")
  })

  it("allows multiple notifications to be marked as read", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@ccrproject.dev" })
    cy.visit("/dashboard")
    cy.dataCy("dropdown_notificiations").click()
    cy.dataCy("dismiss_all_notifications").click()
    cy.dataCy("notification_list_item").eq(0).should("not.have.class", "unread")
    cy.dataCy("notification_list_item").eq(1).should("not.have.class", "unread")
  })
})

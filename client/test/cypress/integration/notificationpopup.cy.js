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
    cy.dataCy("notification_list_item").eq(0).should("have.class", "unread").click()
    cy.dataCy("notification_list_item").eq(0).should("not.have.class", "unread")
  })

  it("allows multiple notifications to be marked as read", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("/submission/review/100")

    cy.interceptGQLOperation("MarkAllNotificationsRead")
    cy.interceptGQLOperation("UpdateSubmissionStatus")

    cy.dataCy("open_for_review").click()

    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait('@UpdateSubmissionStatus')
    cy.login({ email: "regularuser@ccrproject.dev" })
    cy.visit("/dashboard")
    cy.dataCy("dropdown_notificiations").click()
    cy.dataCy("dismiss_all_notifications").click()
    cy.wait("@MarkAllNotificationsRead")
    cy.dataCy("notification_list_item").eq(0).should("not.have.class", "unread")
    cy.dataCy("notification_list_item").eq(1).should("not.have.class", "unread")
  })
})

/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-file-upload"

describe("Notification Popup", () => {
  it("allows an individual notification to be marked as read", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("/dashboard")
    cy.dataCy("dropdown_notificiations").click()
    cy.dataCy("notification_list_item").eq(0).should("have.class", "unread").click()
    cy.dataCy("notification_list_item").eq(0).should("not.have.class", "unread")
  })

  it("allows multiple notifications to be marked as read", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("/submission/108/review")
    cy.interceptGQLOperation("UpdateSubmissionStatus")
    cy.dataCy("status-dropdown").click()
    cy.dataCy("open_for_review").click()
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait('@UpdateSubmissionStatus')
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("/")
    cy.dataCy("dropdown_notificiations").click()
    cy.interceptGQLOperation("MarkAllNotificationsRead")
    cy.dataCy("dismiss_all_notifications").click()
    cy.wait("@MarkAllNotificationsRead")
    cy.dataCy("notification_list_item").eq(0).should("not.have.class", "unread")
    cy.dataCy("notification_list_item").eq(1).should("not.have.class", "unread")
  })
})

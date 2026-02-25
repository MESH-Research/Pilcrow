/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />

// import { a11yLogViolations } from "../../support/helpers"

describe("Comments", () => {
  beforeEach(() => {
    cy.task("resetDb")
  })

  it("should notify a submitter of a overall comment", () => {
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("/submission/100/review")
    cy.interceptGQLOperation("CreateOverallComment")
    cy.dataCy("overallCommentEditor").type("Comment")
    cy.dataCy("overallCommentEditor").find("button[type=submit]").click()
    cy.wait("@CreateOverallComment")
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("/feed")
    cy.dataCy("notification_list_item")
      .eq(0)
      .should("have.class", "unread")
      .contains(
        "Reviewer for Submission added a new overall comment to Pilcrow Test Submission 1"
      )
  })

  it("should notify an overall comment author of a reply", () => {
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("/submission/100/review")
    cy.interceptGQLOperation("CreateOverallCommentReply")
    cy.dataCy("showRepliesButton").eq(3).click()
    cy.dataCy("overallCommentReplyButton").eq(1).click()
    cy.dataCy("overallCommentEditor").eq(0).type("Comment")
    cy.dataCy("overallCommentEditor").eq(0).find("button[type=submit]").click()
    cy.wait("@CreateOverallCommentReply")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("/feed")
    cy.dataCy("notification_list_item")
      .eq(0)
      .should("have.class", "unread")
      .contains(
        "Reviewer for Submission replied to an overall comment for Pilcrow Test Submission 1"
      )
  })

  // TODO:
  // notify an overall comment reply author of a reply
  // notify other overall comment reply authors of a reply to a parent comment
  // notify other overall comment reply authors of a reply to a reply to a parent comment
  // notify review coordinators of added overall comments

  // notify an inline comment author of a reply
  // notify an inline comment reply author of a reply
  // notify other inline comment reply authors of a reply to a parent comment
  // notify other inline comment reply authors of a reply to a reply to a parent comment
  // notify review coordinators of added inline comments
})

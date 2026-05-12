/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />

// import { a11yLogViolations } from "../../support/helpers"

describe("Notifications for Comments", () => {
  beforeEach(() => {
    cy.task("resetDb")
  })

  it("should notify intended recipients of an overall comment", () => {
    // Intended Recipients
    // - Submitters
    // - Review Coordinators
    const notification_message =
      "Reviewer for Submission added a new overall comment to Pilcrow Test Submission 1"
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("/submission/100/review")
    cy.interceptGQLOperation("CreateOverallComment")
    cy.dataCy("overallCommentEditor").type("Comment")
    cy.dataCy("overallCommentEditor").find("button[type=submit]").click()
    cy.wait("@CreateOverallComment")
    cy.dataCy("overallComment").last().contains("Comment")
    // Submitter
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("/feed")
    cy.dataCy("notification_list_item")
      .eq(0)
      .should("have.class", "unread")
      .contains(notification_message)
    // Review Coordinator
    cy.login({ email: "reviewcoordinator@meshresearch.net" })
    cy.visit("/feed")
    cy.dataCy("notification_list_item")
      .eq(0)
      .should("have.class", "unread")
      .contains(notification_message)
  })

  it("should notify intended recipients of an overall comment reply", () => {
    // Intended Recipients
    // - Submitters
    // - Parent Commenter
    // - Other Commenters of the Parent Comment
    // - Review Coordinators
    const notification_message =
      "Reviewer for Submission replied to an overall comment for Pilcrow Test Submission 1"
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("/submission/100/review")
    cy.interceptGQLOperation("CreateOverallCommentReply")
    cy.dataCy("showRepliesButton").last().click()
    cy.dataCy("overallCommentReplyButton").last().click()
    cy.dataCy("overallCommentReplyEditor").type("Comment")
    cy.dataCy("overallCommentReplyEditor").find("button[type=submit]").click()
    cy.wait("@CreateOverallCommentReply")
    cy.dataCy("overallComment").last().contains("Comment")
    // Submitter
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("/feed")
    cy.dataCy("notification_list_item")
      .eq(0)
      .should("have.class", "unread")
      .contains(notification_message)
    // Parent Commenter
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("/feed")
    cy.dataCy("notification_list_item")
      .eq(0)
      .should("have.class", "unread")
      .contains(notification_message)
    // Other Commenter of the Parent Comment
    cy.login({ email: "publicationadministrator@meshresearch.net" })
    cy.visit("/feed")
    cy.dataCy("notification_list_item")
      .eq(0)
      .should("have.class", "unread")
      .contains(notification_message)
    // Review Coordinator
    cy.login({ email: "reviewcoordinator@meshresearch.net" })
    cy.visit("/feed")
    cy.dataCy("notification_list_item")
      .eq(0)
      .should("have.class", "unread")
      .contains(notification_message)
  })

  it("should notify intended recipients of an inline comment reply", () => {
    // Intended Recipients
    // - Submitters
    // - Parent Commenter
    // - Other Commenters of the Parent Comment
    // - Review Coordinators
    const notification_message =
      "Reviewer for Submission replied to an inline comment for Pilcrow Test Submission 1"
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("/submission/100/review")
    cy.dataCy("toggleInlineCommentsButton").click()
    cy.dataCy("inlineComment").eq(0).find("[data-cy=showRepliesButton]").click()
    cy.dataCy("inlineCommentReplyButton").eq(0).click()
    cy.interceptGQLOperation("CreateInlineCommentReply")
    cy.dataCy("inlineCommentReplyEditor").type("Comment")
    cy.dataCy("inlineCommentReplyEditor").find("button[type=submit]").click()
    cy.wait("@CreateInlineCommentReply")
    cy.dataCy("inlineComment")
      .eq(0)
      .find("[data-cy=inlineCommentReply]")
      .last()
      .contains("Comment")

    // Submitter
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("/feed")
    cy.dataCy("notification_list_item")
      .eq(0)
      .should("have.class", "unread")
      .contains(notification_message)
    // Parent Commenter
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("/feed")
    cy.dataCy("notification_list_item")
      .eq(0)
      .should("have.class", "unread")
      .contains(notification_message)
    // Other Commenter of the Parent Comment
    cy.login({ email: "publicationadministrator@meshresearch.net" })
    cy.visit("/feed")
    cy.dataCy("notification_list_item")
      .eq(0)
      .should("have.class", "unread")
      .contains(notification_message)
    // Review Coordinator
    cy.login({ email: "reviewcoordinator@meshresearch.net" })
    cy.visit("/feed")
    cy.dataCy("notification_list_item")
      .eq(0)
      .should("have.class", "unread")
      .contains(notification_message)
  })

  // TODO: Create this testing once E2E inline comment adding is implemented
  // - Notify intended recipients of an inline comment
  //     - Submitters
  //     - Review Coordinators
})

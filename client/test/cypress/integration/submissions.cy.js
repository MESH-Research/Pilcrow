/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-file-upload";
import { a11yLogViolations } from '../support/helpers';

describe('Submission creation', () => {
  it("directs users to a publication's form to create draft submissions", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@pilcrow.dev" })
    cy.visit("submissions")
    cy.injectAxe()
    cy.dataCy("publications_select")
    cy.checkA11y(null, null, a11yLogViolations)
    cy.dataCy("new_submission_title_input").type(
      "Submission from Cypress{enter}"
    )
    cy.dataCy("new_submission_publication_input").click()
    cy.get(".publication_options").contains("Pilcrow Test Publication 1").click()
    cy.dataCy("new_submission_file_upload_input").attachFile("test.txt")
    cy.dataCy("save_submission").click()
    cy.dataCy("submissions_list").contains("Submission from Cypress")
    cy.dataCy("create_submission_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")
    cy.get(".q-notification--top-enter-active").should("not.exist")
    cy.dataCy("submission_link").contains("Submission from Cypress").click()
    cy.dataCy("submitters_list").contains("applicationadministrator@pilcrow.dev")
    cy.checkA11y(null, null, a11yLogViolations)
  })
});

describe("Submissions Page", () => {


  //TODO: If this is checked at the jest and/or laravel level, it doesn't need to be checked here
  it("prevents submission creation when the title exceeds the maximum length", () => {
    const name_520_characters =
      "1234567890".repeat(52)
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@pilcrow.dev" })
    cy.visit("submissions")
    cy.dataCy("new_submission_title_input").type(
      name_520_characters + "{enter}"
    )
    cy.dataCy("create_submission_notify")
      .should("be.visible")
      .should("have.class", "bg-negative")
    cy.get(".q-notification--top-enter-active").should("not.exist")
  })

  it("should allow an application administrator to accept a submission for review and permit reviewers access", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@pilcrow.dev" })
    cy.visit("submissions")

    cy.interceptGQLOperation("UpdateSubmissionStatus")

    cy.dataCy("submission_actions").eq(1).click()
    cy.dataCy("change_status").click()
    cy.dataCy("accept_for_review").click()
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@UpdateSubmissionStatus")
    cy.dataCy("change_status_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")
    cy.login({ email: "reviewer@pilcrow.dev" })
    cy.visit("submission/review/101")
    cy.url().should("not.include", "/error403")
  })

  it("should deny a reviewer from accepting a submission for review", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@pilcrow.dev" })
    cy.visit("submissions")

    cy.interceptGQLOperation("UpdateSubmissionStatus")

    cy.dataCy("submission_actions").eq(1).click()
    cy.dataCy("change_status").click()
    cy.dataCy("accept_for_review").click()

    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@UpdateSubmissionStatus")
    cy.dataCy("change_status_notify")
      .should("be.visible")
      .should("have.class", "bg-negative")
    cy.visit("submission/review/101")
    cy.url().should("include", "/error403")
  })

  it("should deny a reviewer from rejecting a submission for review", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@pilcrow.dev" })
    cy.visit("submissions")

    cy.interceptGQLOperation("UpdateSubmissionStatus")

    cy.dataCy("submission_actions").eq(1).click()
    cy.dataCy("change_status").click()
    cy.dataCy("accept_for_review").click()
    cy.intercept("/graphql").as("graphQL")
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@UpdateSubmissionStatus")
    cy.dataCy("change_status_notify")
      .should("be.visible")
      .should("have.class", "bg-negative")
    cy.visit("submission/review/101")
    cy.url().should("include", "/error403")
  })

  it("should deny a reviewer from changing the status of rejected submissions", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@pilcrow.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(2).click()
    cy.dataCy("change_status").should('have.class', 'disabled')
    cy.dataCy("change_status_item_section").trigger('mouseenter')
    cy.dataCy("cannot_change_submission_status_tooltip")
  })

  it("should deny an application administrator from changing the status of rejected submissions", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@pilcrow.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(2).click()
    cy.dataCy("change_status").should('have.class', 'disabled')
    cy.dataCy("change_status_item_section").trigger('mouseenter')
    cy.dataCy("cannot_change_submission_status_tooltip")
  })

  it("should deny a reviewer from accessing rejected submissions", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@pilcrow.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(2).click()
    cy.dataCy("review").should('have.class', 'disabled')
    cy.dataCy("review").trigger('mouseenter')
    cy.dataCy("cannot_access_submission_tooltip")
    cy.visit("submission/review/102")
    cy.url().should("include", "/error403")
  })

  it("should allow an application administrator to access rejected submissions", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@pilcrow.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(2).click()
    cy.dataCy("review").click()
    cy.url().should("include", "/submission/review")
    cy.dataCy("submussion_title")
  })

  it("should deny a reviewer from changing the status of submissions requested for resubmission", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@pilcrow.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(3).click()
    cy.dataCy("change_status").should('have.class', 'disabled')
    cy.dataCy("change_status_item_section").trigger('mouseenter')
    cy.dataCy("cannot_change_submission_status_tooltip")
    cy.dataCy("change_status_item_section").trigger('click')
    cy.dataCy("change_status_dropdown").should("not.be.visible")
  })

  it("should deny a reviewer from accessing submissions requested for resubmission", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@pilcrow.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(3).click()
    cy.dataCy("review").should('have.class', 'disabled')
    cy.dataCy("review").trigger('mouseenter')
    cy.dataCy("cannot_access_submission_tooltip")
    cy.visit("submission/review/101")
    cy.url().should("include", "/error403")
  })

  it("should allow an application administrator to access submissions requested for resubmission", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@pilcrow.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(3).click()
    cy.dataCy("review").click()
    cy.url().should("include", "/submission/review")
    cy.dataCy("submussion_title")
  })

  it("should allow the submission in draft status be visible to the submitter", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@pilcrow.dev" })
  })
  it("should assert the page is accessible", () => {
    cy.visit("/submissions")
    cy.injectAxe()
    cy.dataCy("submissions_title")
    cy.checkA11y(null, null, a11yLogViolations)
  })
  it("enables access to the Submission Export page under the correct conditions", () => {
    cy.visit("/submissions")
    // Show All Records
    cy.dataCy("submissions_table").contains("Records per page").next().click()
    cy.get("[role='listbox']").contains("All").click()
    // Under Review
    cy.dataCy("submission_actions").first().click()
    cy.dataCy("export_submission").should("have.class","disabled")
    // Initially Submitted
    cy.dataCy("submission_actions").eq(1).click()
    cy.dataCy("export_submission").should("have.class","disabled")
    // Rejected
    cy.dataCy("submission_actions").eq(2).click()
    cy.dataCy("export_submission").should("not.have.class","disabled")
    // Resubmission Requested
    cy.dataCy("submission_actions").eq(3).click()
    cy.dataCy("export_submission").should("not.have.class","disabled")
    // Draft
    cy.dataCy("submission_actions").eq(4).click()
    cy.dataCy("export_submission").should("have.class","disabled")
    // Accepted as Final
    cy.dataCy("submission_actions").eq(5).click()
    cy.dataCy("export_submission").should("not.have.class","disabled")
    // Expired
    cy.dataCy("submission_actions").eq(6).click()
    cy.dataCy("export_submission").should("not.have.class","disabled")
    // Awaiting Decision
    cy.dataCy("submission_actions").eq(7).click()
    cy.dataCy("export_submission").should("have.class","disabled")
    // Awaiting Review
    cy.dataCy("submission_actions").eq(8).click()
    cy.dataCy("export_submission").should("have.class","disabled")
    // Archived
    cy.dataCy("submission_actions").eq(9).click()
    cy.dataCy("export_submission").should("not.have.class","disabled")
    // Deleted
    cy.dataCy("submission_actions").eq(10).click()
    cy.dataCy("export_submission").should("have.class","disabled")
  })
})

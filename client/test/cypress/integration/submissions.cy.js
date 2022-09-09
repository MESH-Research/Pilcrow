/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"
import { a11yLogViolations } from '../support/helpers'
import "cypress-file-upload"

describe("Submissions Page", () => {
  it("creates new submissions", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submissions")
    cy.injectAxe()
    cy.dataCy("new_submission_title_input")
    cy.checkA11y(null, null, a11yLogViolations)
    cy.dataCy("new_submission_title_input").type(
      "Submission from Cypress{enter}"
    )
    cy.dataCy("new_submission_publication_input").click()
    cy.get(".publication_options").contains("CCR Test Publication 1").click()
    cy.dataCy("new_submission_file_upload_input").attachFile("test.txt")
    cy.dataCy("save_submission").click()
    cy.dataCy("submissions_list").contains("Submission from Cypress")
    cy.dataCy("create_submission_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")
    cy.get(".q-notification--top-enter-active").should("not.exist")
    cy.dataCy("submission_link").contains("Submission from Cypress").click()
    cy.dataCy("submitters_list").contains("applicationadministrator@ccrproject.dev")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  //TODO: If this is checked at the jest and/or laravel level, it doesn't need to be checked here
  it("prevents submission creation when the title exceeds the maximum length", () => {
    const name_520_characters =
      "1234567890".repeat(52)
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
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
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(1).click()
    cy.dataCy("change_status").click()
    cy.dataCy("accept_for_review").click()
    cy.intercept("/graphql").as("graphQL")
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@graphQL")
    cy.dataCy("change_status_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submission/review/101")
    cy.url().should("not.include", "/error403")
  })

  it("should deny a reviewer from accepting a submission for review", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(1).click()
    cy.dataCy("change_status").click()
    cy.dataCy("accept_for_review").click()
    cy.intercept("/graphql").as("graphQL")
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@graphQL")
    cy.dataCy("change_status_notify")
      .should("be.visible")
      .should("have.class", "bg-negative")
    cy.visit("submission/review/101")
    cy.url().should("include", "/error403")
  })

  it("should deny a reviewer from rejecting a submission for review", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(1).click()
    cy.dataCy("change_status").click()
    cy.dataCy("accept_for_review").click()
    cy.intercept("/graphql").as("graphQL")
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@graphQL")
    cy.dataCy("change_status_notify")
      .should("be.visible")
      .should("have.class", "bg-negative")
    cy.visit("submission/review/101")
    cy.url().should("include", "/error403")
  })

  it("should deny a reviewer from changing the status of rejected submissions", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(2).click()
    cy.dataCy("change_status").should('have.class', 'disabled')
    cy.dataCy("change_status_item_section").trigger('mouseenter')
    cy.dataCy("cannot_change_submission_status_tooltip")
  })

  it("should deny an application administrator from changing the status of rejected submissions", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(2).click()
    cy.dataCy("change_status").should('have.class', 'disabled')
    cy.dataCy("change_status_item_section").trigger('mouseenter')
    cy.dataCy("cannot_change_submission_status_tooltip")
  })

  it("should deny a reviewer from accessing rejected submissions", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
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
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(2).click()
    cy.dataCy("review").click()
    cy.url().should("include", "/submission/review")
    cy.dataCy("submussion_title")
  })

  it("should deny a reviewer from changing the status of submissions requested for resubmission", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
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
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(3).click()
    cy.dataCy("review").should('have.class', 'disabled')
    cy.dataCy("review_link").trigger('mouseenter')
    cy.dataCy("cannot_access_submission_tooltip")
    cy.visit("submission/review/101")
    cy.url().should("include", "/error403")
  })

  it("should allow an application administrator to access submissions requested for resubmission", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").eq(3).click()
    cy.dataCy("review").click()
    cy.url().should("include", "/submission/review")
    cy.dataCy("submussion_title")
  })

  it("should allow an application administrator to open and close a review", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").first().click()
    cy.dataCy("change_status").click()
    cy.dataCy("open_review").click()
    cy.intercept("/graphql").as("graphQL")
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@graphQL")
    cy.dataCy("change_status_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")
    cy.dataCy("accept_as_final")
    cy.dataCy("close_review").click()
    cy.intercept("/graphql").as("graphQL")
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@graphQL")
  })
})

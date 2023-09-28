/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from "../support/helpers"
import "cypress-file-upload";

describe("Submission Draft Page", () => {
  it("allows content to be added from a text input", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("/submission/104/draft")
    cy.injectAxe()
    cy.dataCy("todo_go_btn").click();
    cy.dataCy("enter_text_option").click();
    cy.dataCy("content_editor").type("Hello World");
    cy.dataCy("submit_entered_text_btn").click();
    cy.dataCy("content_submit_success_btn").click();
    cy.dataCy("todo_preview_btn").eq(0).contains("Preview")
    cy.dataCy("todo_content_btn").eq(0).contains("Edit")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("allows content to be added from a file upload", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("/submission/104/draft")
    cy.injectAxe()
    cy.dataCy("todo_go_btn").click();
    cy.dataCy("upload_option").click();
    cy.dataCy('file_picker').attachFile('test.txt');
    cy.dataCy("submit_upload_btn").click();
    cy.dataCy("content_submit_success_btn").click();
    cy.dataCy("todo_preview_btn").eq(0).contains("Preview")
    cy.dataCy("todo_content_btn").eq(0).contains("Edit")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow a submitter to preview a draft submission before submitting it for review", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@pilcrow.dev" })
    cy.visit("submission/111/draft")
    cy.dataCy("todo_preview_btn").click()
    cy.url().should("include", "/111/preview")
    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should be able to submit for review a draft submission and allow editors to access the submission", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@pilcrow.dev" })
    cy.visit("submission/111/draft")
    cy.dataCy("submit_for_review_btn").click()
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.dataCy("visit_submission_btn").click()

    cy.login({ email: "publicationeditor@pilcrow.dev" })
    cy.visit("submission/111/view")
    cy.url().should("not.include", "/error403")
  })
})

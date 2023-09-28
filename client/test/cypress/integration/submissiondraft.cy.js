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
    cy.dataCy("paste_option").click();
    cy.dataCy("content_editor").type("Hello World");
    cy.dataCy("submit_paste_btn").click();
    cy.dataCy("content_submit_success_btn").click();
    cy.dataCy("todo_done_btn").eq(0).contains("Done")
    cy.dataCy("todo_icon").eq(0).should("have.class", "text-positive")
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
    cy.dataCy("todo_done_btn").eq(0).contains("Done")
    cy.dataCy("todo_icon").eq(0).should("have.class", "text-positive")
    cy.checkA11y(null, null, a11yLogViolations)
  })
})

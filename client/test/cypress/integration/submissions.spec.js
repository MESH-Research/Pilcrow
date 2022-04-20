/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"
import { a11yLogViolations } from '../support/helpers'
import "cypress-file-upload"

describe("Submissions", () => {
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
    cy.dataCy("list_assigned_submitters").contains(
      "applicationadministrator@ccrproject.dev"
    )
    cy.checkA11y(null, {
      rules: {
        "nested-interactive": { enabled: false },
      },
    }, a11yLogViolations)
    cy.reload()
    cy.dataCy("notification_indicator").should("be.visible")
  })

  it("prevents submission creation when the title exceeds the maximum length", () => {
    const name_513_characters =
      "123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123"
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submissions")
    cy.dataCy("new_submission_title_input").type(
      name_513_characters + "{enter}"
    )
    cy.dataCy("create_submission_notify")
      .should("be.visible")
      .should("have.class", "bg-negative")
    cy.get(".q-notification--top-enter-active").should("not.exist")
  })
})

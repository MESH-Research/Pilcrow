/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from "../support/helpers"

describe("Submission Create Page", () => {
  it("prevents submission creation when fields are invalid", () => {
    const name_520_characters = "1234567890".repeat(52)
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("/publication/1/create")
    cy.injectAxe()

    // Title must not exceed 512 characters
    cy.dataCy("new_submission_title_input").type(name_520_characters)
    cy.dataCy("acknowledgement_checkbox").click()
    cy.dataCy("create_submission_btn").click()
    cy.dataCy("new_submission_title_input")
      .closest("label")
      .should("have.class", "q-field--error")
    cy.dataCy("create_submission_form").contains(
      "The maximum length has been exceeded for the title"
    )

    // Title must not be empty
    cy.dataCy("new_submission_title_input").clear()
    cy.dataCy("create_submission_btn").click()
    cy.dataCy("new_submission_title_input")
      .closest("label")
      .should("have.class", "q-field--error")
    cy.dataCy("create_submission_form").contains(
      "A title is required to create a submission."
    )

    // Acknowledgement must be checked
    cy.dataCy("new_submission_title_input").type("Test Submission")
    cy.dataCy("acknowledgement_checkbox").click()
    cy.dataCy("create_submission_btn").click()
    cy.dataCy("acknowledgement_checkbox")
      .closest("label")
      .should("have.class", "q-field--error")
    cy.dataCy("create_submission_form").contains(
      "You must acknowledge you have read and understand the guidelines."
    )

    // Confirm Notify notification is visible
    cy.dataCy("submission_create_notify")
      .should("be.visible")
      .should("have.class", "bg-negative")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("allows submission creation", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("/publication/1/create")
    cy.injectAxe()
    cy.dataCy("new_submission_title_input").type("Test Submission")
    cy.dataCy("acknowledgement_checkbox").click()
    cy.dataCy("create_submission_btn").click()
    cy.dataCy("submission_title").contains("Test Submission")
  })
})

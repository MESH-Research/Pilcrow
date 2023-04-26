/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from '../support/helpers'
import "cypress-file-upload"

describe("Submissions Page", () => {
  beforeEach(() => {
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
    cy.dataCy("export_submission").should("not.exist")
    // Initially Submitted
    cy.dataCy("submission_actions").eq(1).click()
    cy.dataCy("export_submission").should("not.exist")
    // Rejected
    cy.dataCy("submission_actions").eq(2).click()
    cy.dataCy("export_submission").should("exist")
    // Resubmission Requested
    cy.dataCy("submission_actions").eq(3).click()
    cy.dataCy("export_submission").should("exist")
    // Draft
    cy.dataCy("submission_actions").eq(4).click()
    cy.dataCy("export_submission").should("not.exist")
    // Accepted as Final
    cy.dataCy("submission_actions").eq(5).click()
    cy.dataCy("export_submission").should("exist")
    // Expired
    cy.dataCy("submission_actions").eq(6).click()
    cy.dataCy("export_submission").should("exist")
    // Awaiting Decision
    cy.dataCy("submission_actions").eq(7).click()
    cy.dataCy("export_submission").should("not.exist")
    // Awaiting Review
    cy.dataCy("submission_actions").eq(8).click()
    cy.dataCy("export_submission").should("not.exist")
    // Archived
    cy.dataCy("submission_actions").eq(9).click()
    cy.dataCy("export_submission").should("exist")
    // Deleted
    cy.dataCy("submission_actions").eq(10).click()
    cy.dataCy("export_submission").should("not.exist")
  })
})

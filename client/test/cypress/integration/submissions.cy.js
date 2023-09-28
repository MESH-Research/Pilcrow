/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from '../support/helpers';

describe("Submissions Page", () => {

  it("should assert the page is accessible", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("/submissions")
    cy.injectAxe()
    cy.dataCy("submissions_title")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("directs users to a publication's submission creation form", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submissions")
    cy.injectAxe()
    cy.interceptGQLOperation("GetPublications")
    cy.wait("@GetPublications")
    cy.qSelect("publications_select").click()
    cy.qSelectItems("publications_select").eq(0).click()
    cy.dataCy("submit_work_btn").click()
    cy.checkA11y(null, null, a11yLogViolations)
    cy.dataCy("submission_create_subheading").contains("Pilcrow Test Publication 1")
  })

  it("should make the submission in draft status invisible to reviewers", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("submissions")
    cy.dataCy("submissions_table").should('not.include.text', 'Draft')
  })

  it("should make the submission in draft status invisible to review coordinators", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@meshresearch.net" })
    cy.visit("submissions")
    cy.dataCy("submissions_table").should('not.include.text', 'Draft')
  })

  it("should make the submission in draft status invisible to editors", () => {
    cy.task("resetDb")
    cy.login({ email: "publicationeditor@meshresearch.net" })
    cy.visit("submissions")
    cy.dataCy("submissions_table").should('not.include.text', 'Draft')
  })

  it("enables access to the Submission Export page for submissionss according to their status", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("/submissions")
    // Show All Records
    cy.dataCy("submissions_table").contains("Records per page").next().click()
    cy.get("[role='listbox']").contains("All").click()
    // Under Review
    cy.contains("Under Review").parent("tr").findCy("submission_actions").click()
    cy.dataCy("export_submission").should("have.class","disabled")
    // Initially Submitted
    cy.contains("Initially Submitted").parent("tr").findCy("submission_actions").click()
    cy.dataCy("export_submission").should("have.class","disabled")
    // Rejected
    cy.contains("Rejected").parent("tr").findCy("submission_actions").click()
    cy.dataCy("export_submission").should("not.have.class","disabled")
    // Resubmission Requested
    cy.contains("Resubmission Requested").parent("tr").findCy("submission_actions").click()
    cy.dataCy("export_submission").should("not.have.class","disabled")
    // Draft
    cy.contains("Draft").parent("tr").findCy("submission_actions").click()
    cy.dataCy("export_submission").should("have.class","disabled")
    // Accepted as Final
    cy.contains("Accepted as Final").parent("tr").findCy("submission_actions").click()
    cy.dataCy("export_submission").should("not.have.class","disabled")
    // Expired
    cy.contains("Expired").parent("tr").findCy("submission_actions").click()
    cy.dataCy("export_submission").should("not.have.class","disabled")
    // Awaiting Decision
    cy.contains("Awaiting Decision").parent("tr").findCy("submission_actions").click()
    cy.dataCy("export_submission").should("have.class","disabled")
    // Awaiting Review
    cy.contains("Awaiting Review").parent("tr").findCy("submission_actions").click()
    cy.dataCy("export_submission").should("have.class","disabled")
    // Archived
    cy.contains("Archived").parent("tr").findCy("submission_actions").click()
    cy.dataCy("export_submission").should("not.have.class","disabled")
    // Deleted
    cy.contains("Deleted").parent("tr").findCy("submission_actions").click()
    cy.dataCy("export_submission").should("have.class","disabled")
  })

  it("enables the correct submission options are visible for submissions marked as ACCEPTED_AS_FINAL", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("submissions")
    // Show All Records
    cy.dataCy("submissions_table").contains("Records per page").next().click()
    cy.get("[role='listbox']").contains("All").click()

    cy.contains("Accepted as Final").parent("tr").findCy("submission_actions").click()
    cy.dataCy("change_status").click()
    cy.dataCy("archive")
  })

  it("enables the correct submission options are visible for submissions marked as ARCHIVED", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("submissions")
    // Show All Records
    cy.dataCy("submissions_table").contains("Records per page").next().click()
    cy.get("[role='listbox']").contains("All").click()

    cy.contains("Archived").parent("tr").findCy("submission_actions").click()
    cy.dataCy("change_status").click()
    cy.dataCy("delete")
  })

  it("enables the correct submission options are visible for submissions marked as ARCHIVED", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("submissions")
    // Show All Records
    cy.dataCy("submissions_table").contains("Records per page").next().click()
    cy.get("[role='listbox']").contains("All").click()

    cy.contains("Deleted").parent("tr").findCy("submission_actions").click()
    cy.dataCy("change_status").should("have.class","disabled")
  })
})

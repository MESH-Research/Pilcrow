/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from '../support/helpers';

describe("Submissions Page", () => {

  it("should assert the page is accessible", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@pilcrow.dev" })
    cy.visit("/submissions")
    cy.injectAxe()
    cy.dataCy("submissions_title")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("directs users to a publication's submission creation form", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@pilcrow.dev" })
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
    cy.login({ email: "reviewer@pilcrow.dev" })
    cy.visit("submissions")
    cy.dataCy("submissions_table").should('not.include.text', 'Draft')
  })

  it("should make the submission in draft status invisible to review coordinators", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@pilcrow.dev" })
    cy.visit("submissions")
    cy.dataCy("submissions_table").should('not.include.text', 'Draft')
  })

  it("should make the submission in draft status invisible to editors", () => {
    cy.task("resetDb")
    cy.login({ email: "publicationeditor@pilcrow.dev" })
    cy.visit("submissions")
    cy.dataCy("submissions_table").should('not.include.text', 'Draft')
  })

  it("enables access to the Submission Export page for submissionss according to their status", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@pilcrow.dev" })
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

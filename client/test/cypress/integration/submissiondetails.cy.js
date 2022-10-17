/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"
import { a11yLogViolations } from '../support/helpers'

describe("Submission Details", () => {
  it("should assert the Submission Details page is accessible", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.injectAxe()
    cy.dataCy("submitters_list")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow assignments of reviewers by application administrators", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.interceptGQLOperation('UpdateSubmissionReviewers');

    cy.dataCy("reviewers_list").within(() => {
      cy.userSearch('input_user', 'applicationAd')
      cy.qSelectItems('input_user').eq(0).click()
      cy.dataCy('button-assign').click();
    })

    cy.wait("@UpdateSubmissionReviewers")
    cy.dataCy("reviewers_list").find(".q-list").contains("Application Administrator")

    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow assignments of reviewers by review coordinators", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.interceptGQLOperation('UpdateSubmissionReviewers')

    cy.dataCy("reviewers_list").within(() => {
      cy.userSearch('input_user', 'applicationAd')
      cy.qSelectItems('input_user').eq(0).click()
      cy.dataCy('button-assign').click()
    })

    cy.wait("@UpdateSubmissionReviewers")
    cy.dataCy("reviewers_list").find(".q-list").contains("Application Administrator")

    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow removal and assignment of review coordinators by application administrators", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.interceptGQLOperation("UpdateSubmissionReviewCoordinators")

    cy.dataCy("coordinators_list")
        .find('.q-list')
        .eq(0)
        .find("[data-cy=button_unassign]")
        .click();
    cy.wait("@UpdateSubmissionReviewCoordinators")

    cy.dataCy("coordinators_list").find(".q-list").should("not.exist")

    cy.dataCy("coordinators_list").within(() => {
      cy.userSearch('input_user', "applicationAd")
      cy.qSelectItems('input_user').eq(0).click()
      cy.dataCy('button-assign').click();
    })
    cy.wait("@UpdateSubmissionReviewCoordinators")
    cy.dataCy("coordinators_list").find('.q-list').contains("Application Administrator")

    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should disallow assignments of duplicate reviewers", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")
    cy.interceptGQLOperation("UpdateSubmissionReviewers")

    cy.dataCy('reviewers_list').within(() => {
      cy.userSearch('input_user', 'applicationAd')
      cy.qSelectItems('input_user').eq(0).click()
      cy.dataCy('button-assign').click()
    })
    cy.wait("@UpdateSubmissionReviewers")

    cy.dataCy('reviewers_list').within(() => {
      cy.userSearch('input_user', 'applicationAd')
      cy.qSelectItems('input_user').eq(0).click()
      cy.dataCy('button-assign').click()
    })
    cy.wait("@UpdateSubmissionReviewers")

    cy.dataCy('reviewers_list').find('.q-item').should('have.length', 2)

  })

  it("should show comments associated with status changes in the Activity section", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submissions")

    cy.interceptGQLOperation("UpdateSubmissionStatus");

    cy.dataCy("submission_actions").first().click()
    cy.dataCy("change_status").click()
    cy.dataCy("open_review").click()
    cy.dataCy("status_change_comment").type("first comment from admin")
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@UpdateSubmissionStatus")

    cy.dataCy("change_status_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")

    cy.dataCy("close_review").click()
    cy.dataCy("status_change_comment").type("second comment from admin")
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@UpdateSubmissionStatus")

    cy.visit("/submission/100")
    cy.dataCy("activity_section").contains("first comment from admin")
    cy.dataCy("activity_section").contains("second comment from admin")
  });

  it("should show the invitation form for a review coordinator", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@ccrproject.dev" })
    cy.visit("/submission/100")
    cy.dataCy("invitation_form")
  });

  it("should hide the invitation form for a reviewer", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("/submission/100")
    cy.dataCy("invitation_form").should("not.exist")
  });
})

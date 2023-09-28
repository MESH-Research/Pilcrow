/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from '../support/helpers'

describe("Submission Details", () => {
  it("should assert the Submission Details page is accessible", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/100/details")
    cy.injectAxe()
    cy.dataCy("submitters_list")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow assignments of reviewers by application administrators", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/100/details")
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
    cy.login({ email: "reviewcoordinator@meshresearch.net" })
    cy.visit("submission/100/details")
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
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/100/details")
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
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/100/details")
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
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/108/review")

    cy.interceptGQLOperation("UpdateSubmissionStatus");

    cy.dataCy("status-dropdown").click()
    cy.dataCy("open_for_review").click()
    cy.dataCy("status_change_comment").type("first comment from admin")
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@UpdateSubmissionStatus")

    cy.dataCy("change_status_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")

    cy.dataCy("close_for_review").click()
    cy.dataCy("status_change_comment").type("second comment from admin")
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@UpdateSubmissionStatus")

    cy.visit("/submission/108/details")
    cy.dataCy("activity_section").contains("first comment from admin")
    cy.dataCy("activity_section").contains("second comment from admin")
  });

  it("should show the invitation form for a review coordinator", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@meshresearch.net" })
    cy.visit("/submission/100/details")
    cy.dataCy("invitation_form")
  });

  it("should hide the invitation form for a reviewer", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("/submission/100/details")
    cy.dataCy("invitation_form").should("not.exist")
  });

  it("should allow review coordinators to invite unregistered reviewers", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@meshresearch.net" })
    cy.visit("submission/100/details")
    cy.interceptGQLOperation('InviteReviewer');
    cy.dataCy("reviewers_list").within(() => {
      cy.userSearch('input_user', 'scholarlystranger@gmail.com')
      cy.dataCy('button-assign').click();
    })
    cy.wait("@InviteReviewer")
    cy.dataCy("reviewers_list").find(".q-list").contains("stranger")
    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should disallow invitations for invalid emails", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@meshresearch.net" })
    cy.visit("submission/100/details")
    cy.interceptGQLOperation('InviteReviewer');
    cy.dataCy("reviewers_list").within(() => {
      cy.userSearch('input_user', 'invalidemail')
      cy.dataCy('button-assign').click();
    })
    cy.wait("@InviteReviewer")
    cy.dataCy('reviewers_list').find('.q-item').should('have.length', 1)
    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow review coordinators to reinvite reviewers", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@meshresearch.net" })
    cy.visit("submission/100/details")
    cy.interceptGQLOperation('InviteReviewer');
    cy.dataCy("reviewers_list").within(() => {
      cy.userSearch('input_user', 'scholarlystranger@gmail.com')
      cy.dataCy('button-assign').click();
    })
    cy.wait("@InviteReviewer")
    cy.dataCy('user_unconfirmed').click();
    cy.dataCy('dirtyYesReinviteUser').click();
    cy.dataCy('reinvite_notify')
      .should("be.visible")
      .should("have.class", "bg-positive")
    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow a submitter to update the title", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("submission/100/details")
    cy.interceptGQLOperation("UpdateSubmissionTitle")
    cy.dataCy("submission_title").click()
    cy.dataCy("submission_title_input").type('Hello World{enter}');
    cy.wait("@UpdateSubmissionTitle")
    cy.dataCy('submission_title').contains("Hello World")
    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("enables access to the Submission Export page under the correct conditions", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    // Under Review
    cy.visit("submission/100/details")
    cy.dataCy("submission_export_btn").should("be.disabled")
    // Rejected
    cy.visit("submission/102/review")
    cy.dataCy("submission_export_btn").should("not.be.disabled")
  })
})

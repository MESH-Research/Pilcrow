/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"
import { a11yLogViolations } from '../support/helpers'

describe("Submissions Details", () => {
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

    cy.dataCy("reviewers_list").within(() => {
      cy.dataCy('input_user').type("applicationAd")
      cy.qSelectItems('input_user').eq(0).click()
      cy.intercept("/graphql").as("graphQL")
      cy.dataCy('button-assign').click();
    })

    cy.wait("@graphQL")
    cy.dataCy("reviewers_list").find(".q-list").contains("Application Administrator")

    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow assignments of reviewers by review coordinators", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@ccrproject.dev" })
    cy.visit("submission/100")

    cy.dataCy("reviewers_list").within(() => {
      cy.dataCy('input_user').type("applicationAd")
      cy.qSelectItems('input_user').eq(0).click()
      cy.intercept("/graphql").as('graphQL')
      cy.dataCy('button-assign').click()
    })

    cy.wait("@graphQL")
    cy.dataCy("reviewers_list").find(".q-list").contains("Application Administrator")

    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow removal and assignment of review coordinators by application administrators", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")

    cy.intercept("/graphql").as("removeCoordinatorFetch")
    cy.dataCy("coordinators_list")
        .find('.q-list')
        .eq(0)
        .find("[data-cy=button_unassign]")
        .click();
    cy.wait("@removeCoordinatorFetch")

    cy.dataCy("coordinators_list").find(".q-list").should("not.exist")

    cy.dataCy("coordinators_list").within(() => {
      cy.intercept("/graphql").as("userListFetch")
      cy.dataCy('input_user').type("applicationAd")
      cy.wait(["@userListFetch", "@userListFetch"])
      cy.qSelectItems('input_user').eq(0).click()
      cy.intercept("/graphql").as("addCoordinatorFetch")
      cy.dataCy('button-assign').click();
    })
    cy.wait("@addCoordinatorFetch")
    cy.dataCy("coordinators_list").find('.q-list').contains("Application Administrator")

    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should disallow assignments of duplicate reviewers", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/100")

    cy.dataCy('reviewers_list').within(() => {
      cy.dataCy('input_user').type("applicationAd")
      cy.qSelectItems('input_user').eq(0).click()
      cy.intercept("/graphql").as("addReviewer1")
      cy.dataCy('button-assign').click()
      cy.wait("@addReviewer1")
    })

    cy.dataCy('reviewers_list').within(() => {
      cy.dataCy('input_user').type("applicationAd")
      cy.qSelectItems('input_user').eq(0).click()
      cy.intercept("/graphql").as("addReviewer2")
      cy.dataCy('button-assign').click()
      cy.wait("@addReviewer2")
    })

    cy.dataCy('reviewers_list').find('.q-item').should('have.length', 2)

  })

  it("should show comments associated with status changes in the Activity section", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submissions")
    cy.dataCy("submission_actions").first().click()
    cy.dataCy("change_status").click()
    cy.dataCy("open_review").click()
    cy.intercept("/graphql").as("graphQL")
    cy.dataCy("status_change_comment").type("comment from admin 1")
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@graphQL")
    cy.dataCy("change_status_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")
    cy.dataCy("accept_as_final")
    cy.dataCy("close_review").click()
    cy.intercept("/graphql").as("graphQL")
    cy.dataCy("status_change_comment").type("comment from admin 2")
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.wait("@graphQL")
    cy.visit("/submission/100")
    cy.dataCy("activity_section").contains("comment from admin 1").contains("comment from admin 2")
  });
})

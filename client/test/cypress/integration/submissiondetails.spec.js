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
})

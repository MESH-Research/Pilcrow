/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"
import { a11yLogViolations } from '../support/helpers'

describe("Submissions Review", () => {
  // it("should assert the Submission Review page is accessible", () => {
  //   cy.task("resetDb")
  //   cy.login({ email: "applicationadministrator@ccrproject.dev" })
  //   cy.visit("submission/review/100")
  //   cy.injectAxe()
  //   cy.dataCy("submission_review_layout")
  //   cy.checkA11y(null, null, a11yLogViolations)
  // })

  // it("should assert the Submission Review page can be accessed from the dashboard", () => {
  //   cy.task("resetDb")
  //   cy.login({ email: "applicationadministrator@ccrproject.dev" })
  //   cy.visit("/dashboard")
  //   cy.dataCy("sidebar_toggle").click()
  //   cy.dataCy("submissions_link").click()
  //   cy.dataCy("submission_link").contains("CCR Test Submission 1").click()
  //   cy.dataCy("submission_review_btn").click()
  //   cy.dataCy("submission_review_page")
  // })

  // it("should display style criteria from the database in the inline comment editor", () => {
  //   cy.task("resetDb")
  //   cy.login({ email: "applicationadministrator@ccrproject.dev" })
  //   cy.visit("submission/review/100")
  //   cy.dataCy("toggleInlineCommentsButton").click()

  //   const criteriaLabels = ["Accessibility", "Relevance", "Coherence", "Scholarly Dialogue"]
  //   cy.dataCy('criteria-item').should('have.length', 4)

  //   cy.dataCy('criteria-item').each(($el, index) => {
  //     cy.wrap($el).contains(criteriaLabels[index])
  //   })

  //   cy.dataCy('criteria-item').first().within(($el) => {
  //     const $toggle = $el.find('.q-toggle')
  //     cy.wrap($toggle).should('have.attr', 'aria-checked', "false")

  //     cy.wrap($el).dataCy('criteria-label').click()
  //     cy.wrap($toggle).should('have.attr', 'aria-checked', "true") //Clicking label toggles input

  //     cy.wrap($el).dataCy('criteria-icon').click()
  //     cy.wrap($toggle).should('have.attr', 'aria-checked', "false") //Clicking icon toggles input

  //     //Expansion item is hidden
  //     cy.wrap($el).dataCy('criteria-description').should('not.be.visible')

  //     //Expansion item becomes visible on click
  //     cy.wrap($el).get('.q-expansion-item__toggle-icon').click()
  //     cy.wrap($el).dataCy('criteria-description').should('be.visible')

  //     //Toggle state is unchanged
  //     cy.wrap($toggle).should('have.attr', 'aria-checked', "false")

  //   })
  // })

  it("should allow a reviewer to submit overall comments", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submission/review/100")
    // An attempt to create an empty overall comment
    cy.dataCy("overallCommentForm").find("[data-cy=submit]").click()
    // Creating an overall comment
    cy.dataCy("overallCommentEditor").type("This is an overall comment.")
    cy.intercept("/graphql").as("addOverallCommentMutation")
    cy.dataCy("overallCommentForm").find("[data-cy=submit]").click()
    cy.wait("@addOverallCommentMutation")
    //   3 overall comment parents already exist from database seeding
    // + 0 disallowed empty overall comment creation attempt
    // + 1 newly created overall comment
    // = 4
    cy.dataCy("overallComment").should('have.length', 4)
    cy.dataCy("overallComment").last().contains("This is an overall comment.")
  })

  it("should allow a reviewer to submit overall comment replies", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submission/review/100")
    cy.dataCy("overallComment").first().find("[data-cy=overallCommentReplyButton]").click()
    // An attempt to create an empty overall comment
    cy.dataCy("overallCommentReplyForm").first().find("[data-cy=submit]").click()
    // Creating an overall comment
    cy.dataCy("overallCommentReplyEditor").first().type("This is a reply to an overall comment.")
    cy.intercept("/graphql").as("addOverallCommentReplyMutation")
    cy.dataCy("overallCommentReplyForm").first().find("[data-cy=submit]").click()
    cy.wait("@addOverallCommentReplyMutation")
    //   9 overall comment replies already exist from database seeding
    // + 0 disallowed empty overall comment reply creation attempt
    // + 1 newly created overall comment reply
    // = 10
    cy.dataCy("overallCommentReply").should('have.length', 10)
    cy.dataCy("overallCommentReply").first().contains("This is a reply to an overall comment.")
  })

  it("should allow a reviewer to submit replies to overall comment replies", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submission/review/100")
    cy.dataCy("overallComment").last().find("[data-cy=overallCommentReply]").last().find("[data-cy=overallCommentReplyButton]").click()
    // An attempt to create an empty reply to an overall comment
    cy.dataCy("overallCommentReplyForm").last().find("[data-cy=submit]").click()
    // Creating an overall comment
    cy.dataCy("overallCommentReplyEditor").last().type("This is a reply to an overall comment reply.")
    cy.intercept("/graphql").as("addOverallCommentReplyMutation")
    cy.dataCy("overallCommentReplyForm").last().find("[data-cy=submit]").click()
    cy.wait("@addOverallCommentReplyMutation")
    //   9 overall comment replies already exist from database seeding
    // + 0 disallowed empty overall comment reply creation attempt
    // + 1 newly created overall comment reply
    // = 10
    cy.dataCy("overallCommentReply").should('have.length', 10)
    cy.dataCy("overallCommentReply").last().contains("This is a reply to an overall comment reply.")
  })

  it("should allow a reviewer to submit inline comment replies", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submission/review/100")
    cy.dataCy("toggleInlineCommentsButton").click()
    cy.dataCy("inlineComment").first().find("[data-cy=inlineCommentReplyButton]").click()
    // An attempt to create an empty inline comment reply
    cy.dataCy("inlineCommentReplyForm").first().find("[data-cy=submit]").click()
    // Creating an inline comment reply
    cy.dataCy("inlineCommentReplyEditor").first().type("This is an inline comment reply.")
    cy.intercept("/graphql").as("addInlineCommentReplyMutation")
    cy.dataCy("inlineCommentReplyForm").first().find("[data-cy=submit]").click()
    cy.wait("@addInlineCommentReplyMutation")
    //   1 inline comment reply already exists on the first inline comment from database seeding
    // + 0 disallowed empty inline comment reply creation attempt
    // + 1 newly created inline comment reply on the first inline comment
    // = 2
    cy.dataCy("inlineComment").first().find("[data-cy=inlineCommentReply]").should('have.length', 2)
    cy.dataCy("inlineComment").first().find("[data-cy=inlineCommentReply]").last().contains("This is an inline comment reply.")
  })
})

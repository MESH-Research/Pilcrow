/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import "cypress-axe"
import { a11yLogViolations } from '../support/helpers'

describe("Submissions Review", () => {
  it("should assert the Submission Review page is accessible", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/review/100")
    cy.injectAxe()
    cy.dataCy("submission_review_layout")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  //TODO: Refactor with text selection etc.
/*   it("should display style criteria from the database in the inline comment editor", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/review/100")
    cy.dataCy("toggleInlineCommentsButton").click()

    const criteriaLabels = ["Accessibility", "Relevance", "Coherence", "Scholarly Dialogue"]
    cy.dataCy('criteria-item').should('have.length', 4)

    cy.dataCy('criteria-item').each(($el, index) => {
      cy.wrap($el).contains(criteriaLabels[index])
    })

    cy.dataCy('criteria-item').first().within(($el) => {
      const $toggle = $el.find('.q-toggle')
      cy.wrap($toggle).should('have.attr', 'aria-checked', "false")

      cy.wrap($el).dataCy('criteria-label').click()
      cy.wrap($toggle).should('have.attr', 'aria-checked', "true") //Clicking label toggles input

      cy.wrap($el).dataCy('criteria-icon').click()
      cy.wrap($toggle).should('have.attr', 'aria-checked', "false") //Clicking icon toggles input

      //Expansion item is hidden
      cy.wrap($el).dataCy('criteria-description').should('not.be.visible')

      //Expansion item becomes visible on click
      cy.wrap($el).get('.q-expansion-item__toggle-icon').click()
      cy.wrap($el).dataCy('criteria-description').should('be.visible')

      //Toggle state is unchanged
      cy.wrap($toggle).should('have.attr', 'aria-checked', "false")

    })
  }) */

  it("should allow a reviewer to submit overall comments", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submission/review/100")
    // Attempt to create an empty overall comment
    cy.dataCy("overallCommentEditor").find("button[type=submit]").click()
    // Create an overall comment
    cy.dataCy("overallCommentEditor").type("This is an overall comment.")
    cy.intercept("/graphql").as("addOverallCommentMutation")
    cy.dataCy("overallCommentEditor").find("button[type=submit]").click()
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
    // Attempt to create an empty overall comment reply
    cy.dataCy("overallCommentReplyEditor").first().find("button[type=submit]").click()
    // Create an overall comment reply
    cy.dataCy("overallCommentReplyEditor").first().type("This is a reply to an overall comment.")
    cy.intercept("/graphql").as("addOverallCommentReplyMutation")
    cy.dataCy("overallCommentReplyEditor").first().find("button[type=submit]").click()
    cy.wait("@addOverallCommentReplyMutation")
    //   0 overall comment replies already exist from database seeding
    // + 0 disallowed empty overall comment reply creation attempt
    // + 1 newly created overall comment reply
    // = 1
    cy.dataCy("overallComment").first().find("[data-cy=showRepliesButton]").click()
    cy.dataCy("overallComment").first().find("[data-cy=overallCommentReply]").should('have.length', 1)
    cy.dataCy("overallComment").first().find("[data-cy=overallCommentReply]").first().contains("This is a reply to an overall comment.")
  })

  it("should allow a reviewer to submit replies to overall comment replies", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submission/review/100")
    cy.dataCy("overallComment").last().find("[data-cy=showRepliesButton]").click()
    cy.dataCy("overallCommentReply").last().find("[data-cy=commentActions]").click()
    cy.dataCy("quoteReply").click()
    cy.dataCy("overallCommentReplyEditor").last().type("This is a reply to an overall comment reply.")
    // Create a reply to an overall comment reply
    cy.intercept("/graphql").as("addOverallCommentReplyMutation")
    cy.dataCy("overallCommentReplyEditor").last().find("button[type=submit]").click()
    cy.wait("@addOverallCommentReplyMutation")
    //   8 overall comment replies are already visible in this thread from database seeding
    // + 0 disallowed empty overall comment reply creation attempt
    // + 1 newly created overall comment reply
    // = 9
    cy.dataCy("overallCommentReply").should('have.length', 9)
    cy.dataCy("overallCommentReply").last().contains("This is a reply to an overall comment reply.")
  })

  it("should allow a reviewer to submit inline comment replies", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submission/review/100")
    cy.dataCy("toggleInlineCommentsButton").click()
    cy.dataCy("inlineComment")
      .first()
      .within((el) => {
        cy.wrap(el).findCy('collapseRepliesButton').click()
        cy.wrap(el).findCy('inlineCommentReplyButton').click()
        cy.dataCy("inlineCommentReplyEditor").type("This is an inline comment reply.")
        cy.intercept("/graphql").as("addInlineCommentReplyMutation")
        cy.dataCy('inlineCommentReplyEditor').find('button[type=submit]').click()

      })
    cy.wait("@addInlineCommentReplyMutation")
    //   1 inline comment reply already exists on the first inline comment from database seeding
    // + 0 disallowed empty inline comment reply creation attempt
    // + 1 newly created inline comment reply on the first inline comment
    // = 2
    cy.dataCy("inlineComment").first().find("[data-cy=inlineCommentReply]").should('have.length', 2)
    cy.dataCy("inlineComment").first().find("[data-cy=inlineCommentReply]").last().contains("This is an inline comment reply.")
  })

  //TODO: Refactor this test with text selection and all that jazz.
/*   it("should allow a reviewer to submit replies to inline comment replies", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submission/review/100")
    cy.dataCy("toggleInlineCommentsButton").click()
    cy.dataCy("inlineCommentReply").last().find("[data-cy=inlineCommentReplyButton]").click()
    // Attempt to create an empty reply to an inline comment reply
    cy.dataCy("inlineCommentReplyEditor").last().find("button[type=submit]").click()
    // Create a reply to an inline comment reply
    cy.dataCy("inlineCommentReplyEditor").first().type("This is a reply to an inline comment reply.")
    cy.intercept("/graphql").as("addInlineCommentReplyMutation")
    cy.dataCy("inlineCommentReplyEditor").first().find("button[type=submit]").click()
    cy.wait("@addInlineCommentReplyMutation")
    //   11 inline comment reples already exist in total from database seeding
    // + 0 disallowed empty inline comment reply creation attempt
    // + 1 newly created inline comment reply on the last inline comment reply
    // = 12
    cy.dataCy("inlineCommentReply").should('have.length', 12)
    cy.dataCy("inlineCommentReply").last().contains("This is a reply to an inline comment reply.")
  }) */

  it("should make inline comments active upon clicks to their corresponding bubble widgets", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submission/review/100")
    cy.dataCy("comment-widget").should('have.length', 3)
    cy.dataCy("comment-widget").each((_, index) => {
      cy.get(".fullscreen.q-drawer__backdrop:not('.hidden')").should('not.exist')
      cy.dataCy("comment-widget").eq(index).click()
      cy.dataCy("inlineComment").eq(index).find('> .q-card').should('have.class', 'active')
      //TODO: Redesign so comment drawer toggle is not hidden when the drawer is visible at small screen sizes.
      cy.get(".fullscreen.q-drawer__backdrop:not('.hidden')").click()
    })
  })

  it("should make inline comments active upon clicks to their corresponding highlights", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submission/review/100")
    cy.dataCy("comment-highlight").each((_, index) => {
      cy.get(".fullscreen.q-drawer__backdrop:not('.hidden')").should('not.exist')
      cy.dataCy("comment-highlight").eq(index).click()
      cy.dataCy("inlineComment").find('> .q-card.active').should('have.length', 1)
      //TODO: Redesign so comment drawer toggle is not hidden when the drawer is visible at small screen sizes.
      cy.get(".fullscreen.q-drawer__backdrop:not('.hidden')").click()
    })
  })

  it("should deny a reviewer access to a submission's contents before it is accepted for review", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submission/review/101")
    cy.url().should("include", "/error403")
  })

  it("should allow an application administrator to accept a submission for review and permit reviewers access", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/review/101")
    cy.dataCy("submission_status").contains("Initially Submitted")
    cy.dataCy("accept_for_review").click()
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.dataCy("submission_status").contains("Awaiting Review")
    cy.login({ email: "reviewer@ccrproject.dev" })
    cy.visit("submission/review/101")
    cy.url().should("not.include", "/error403")
  })

  // TODO update seeder with subission that is awaiting review
  it("should allow an application administrator to open a review, close a review, and that final decision options are visible", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("submission/review/101")
    cy.dataCy("accept_for_review").click()
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.dataCy("submission_status").contains("Awaiting Review")
    cy.dataCy("open_for_review").click()
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.dataCy("submission_status").contains("Under Review")
    cy.dataCy("decision_options")
    cy.dataCy("close_for_review").click()
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.dataCy("submission_status").contains("Awaiting Decision")
    cy.dataCy("decision_options")
  })
})

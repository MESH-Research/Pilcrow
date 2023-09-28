/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import { a11yLogViolations } from '../support/helpers'

describe("Submissions Review", () => {
  it("should assert the Submission Review page is accessible", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/100/review")
    cy.injectAxe()
    cy.dataCy("submission_review_layout")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  //TODO: Refactor with text selection etc.
/*   it("should display style criteria from the database in the inline comment editor", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/100/review")
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
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("submission/100/review")

    cy.interceptGQLOperation('CreateOverallComment')

    // Attempt to create an empty overall comment
    cy.dataCy("overallCommentEditor").find("button[type=submit]").click()
    // Create an overall comment
    cy.dataCy("overallCommentEditor").type("This is an overall comment.")
    cy.dataCy("overallCommentEditor").find("button[type=submit]").click()

    cy.wait("@CreateOverallComment")
    //   3 overall comment parents already exist from database seeding
    // + 0 disallowed empty overall comment creation attempt
    // + 1 newly created overall comment
    // = 4
    cy.dataCy("overallComment").should('have.length', 4)
    cy.dataCy("overallComment").last().contains("This is an overall comment.")
  })

  it("should allow a reviewer to submit overall comment replies", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("submission/100/review")

    cy.interceptGQLOperation("CreateOverallCommentReply")

    cy.dataCy("overallComment").first().find("[data-cy=overallCommentReplyButton]").click()
    // Attempt to create an empty overall comment reply
    cy.dataCy("overallCommentReplyEditor").first().find("button[type=submit]").click()
    // Create an overall comment reply
    cy.dataCy("overallCommentReplyEditor").first().type("This is a reply to an overall comment.")

    cy.dataCy("overallCommentReplyEditor").first().find("button[type=submit]").click()
    cy.wait("@CreateOverallCommentReply")
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
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("submission/100/review")

    cy.interceptGQLOperation("CreateOverallCommentReply")
    cy.dataCy("overallComment").last().find("[data-cy=showRepliesButton]").click()
    cy.dataCy("overallCommentReply").last().find("[data-cy=commentActions]").click()
    cy.dataCy("quoteReply").click()
    cy.dataCy("overallCommentReplyEditor").last().type("This is a reply to an overall comment reply.")
    // Create a reply to an overall comment reply

    cy.dataCy("overallCommentReplyEditor").last().find("button[type=submit]").click()
    cy.wait("@CreateOverallCommentReply")
    //   8 overall comment replies are already visible in this thread from database seeding
    // + 0 disallowed empty overall comment reply creation attempt
    // + 1 newly created overall comment reply
    // = 9
    cy.dataCy("overallCommentReply").should('have.length', 9)
    cy.dataCy("overallCommentReply").last().contains("This is a reply to an overall comment reply.")
  })

  it("should allow a reviewer to submit inline comment replies", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("submission/100/review")

    cy.interceptGQLOperation("CreateInlineCommentReply")

    cy.dataCy("toggleInlineCommentsButton").click()
    cy.dataCy("inlineComment")
      .first()
      .within((el) => {
        cy.wrap(el).findCy('showRepliesButton').click()
        cy.wrap(el).findCy('inlineCommentReplyButton').click()
        cy.dataCy("inlineCommentReplyEditor").type("This is an inline comment reply.")
        cy.dataCy('inlineCommentReplyEditor').find('button[type=submit]').click()

      })
    cy.wait("@CreateInlineCommentReply")
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
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("submission/100/review")
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
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("submission/100/review")
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
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("submission/100/review")
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
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("submission/101/review")
    cy.url().should("include", "/error403")
  })

  it("should allow an application administrator to accept a submission for review and permit reviewers access", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/101/review")
    cy.dataCy("submission_status").contains("Initially Submitted")
    cy.dataCy("status-dropdown").click()
    cy.dataCy("accept_for_review").click()
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.dataCy("submission_status").contains("Awaiting Review")
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("submission/101/review")
    cy.url().should("not.include", "/error403")
  })

  it("should allow an application administrator to open a review, close a review, and that final decision options are visible", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/108/review")
    cy.dataCy("submission_status").contains("Awaiting Review")
    cy.dataCy("status-dropdown").click()
    cy.dataCy("open_for_review").click()
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.visit("submission/108/review")
    cy.dataCy("submission_status").contains("Under Review")
    cy.dataCy("status-dropdown").click()
    cy.dataCy("close_for_review").click()
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.visit("submission/108/review")
    cy.dataCy("submission_status").contains("Awaiting Decision")
    cy.dataCy("status-dropdown").click()
    cy.dataCy("decision_options")
  })

  it("should display an explanation and redirect button for a draft submission with no content", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("submission/104/review")
    cy.dataCy("explanation")
    cy.dataCy("draft_btn").click()
    cy.url().should("include", "/submission/104/draft")
  })

  it("should be able to submit for review a draft submission with content and allow reviewers to access the submission", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    cy.visit("submission/111/review")
    cy.dataCy("status-dropdown").click()
    cy.dataCy("initially_submit").click()
    cy.dataCy("dirtyYesChangeStatus").click()

    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("submission/111/review")
    cy.url().should("not.include", "/error403")
  })

  it("should not display the decision options for rejected submissions", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/102/review")
    cy.dataCy("submission_status").contains("Rejected")
    cy.dataCy("decision_options").should('not.exist');
  })

  it("should not display the decision options for submissions requested for resubmission", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/103/review")
    cy.dataCy("submission_status").contains("Resubmission Requested")
    cy.dataCy("decision_options").should('not.exist');
  })

  it("should allow overall comments to be modified", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/100/review")

    // create new overall comment
    cy.interceptGQLOperation('CreateOverallComment')
    cy.dataCy("overallCommentEditor").type("This is an overall comment.")
    cy.dataCy("overallCommentEditor").first().find("button[type=submit]").click()
    cy.wait("@CreateOverallComment")

    // click on modifyComment
    cy.dataCy("overallComment")
      .should('have.length', 4)
      .last()
      .find("[data-cy=commentActions]")
      .click()

    cy.dataCy("modifyComment").click()

    // modify, submit
    cy.dataCy("modifyOverallCommentEditor").type("This is a modified overall comment.")
    cy.dataCy("modifyOverallCommentEditor").find("button[type=submit]").click()

    // verify comment includes "This is a modified overall comment."
    cy.dataCy("overallComment").last().contains("This is a modified overall comment")
  })

  it("should allow overall comment replies to be modified", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/100/review")

    // create new comment reply
    cy.interceptGQLOperation("CreateOverallCommentReply")
    cy.dataCy("overallComment").first().find("[data-cy=overallCommentReplyButton]").click()
    cy.dataCy("overallCommentReplyEditor").first().type("This is a reply to an overall comment.")
    cy.dataCy("overallCommentReplyEditor").first().find("button[type=submit]").click()
    cy.wait("@CreateOverallCommentReply")

    cy.dataCy("overallComment").first().find("[data-cy=showRepliesButton]").click()
    cy.dataCy("overallCommentReply").first().find("[data-cy=commentActions]").click()
    cy.dataCy("modifyComment").click()

    // modify, submit
    cy.dataCy("modifyOverallCommentReplyEditor").type("This is a modified overall comment reply.")
    cy.dataCy("modifyOverallCommentReplyEditor").find("button[type=submit]").click()

    // verify comment includes "This is a modified overall comment reply."
    cy.dataCy("overallCommentReply").first().contains("This is a modified overall comment reply.")
  })

  it("should allow inline comments to be modified", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/100/review")

    cy.dataCy("toggleInlineCommentsButton").click()

    cy.dataCy("inlineComment").first().find("[data-cy=commentActions]").click()
    cy.dataCy("modifyComment").click()

    // modify, submit
    cy.dataCy("comment-editor").first().type("This is a modified inline comment.")
    cy.dataCy("criteria-item").last().click()
    cy.dataCy("modifyInlineCommentEditor").find("button[type=submit]").click()

    // verify comment includes "This is a modified inline comment."
    cy.dataCy("inlineComment").first().contains("This is a modified inline comment.")
  })

  it("should allow inline comment replies to be modified", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/100/review")

    cy.dataCy("toggleInlineCommentsButton").click()
    cy.dataCy("inlineComment").eq(1).find("[data-cy=inlineCommentReplyButton]").click()

    // create new inline comment reply
    cy.interceptGQLOperation("CreateInlineCommentReply")
    cy.dataCy("inlineCommentReplyEditor").type("This is a new inline comment reply.")
    cy.dataCy("inlineCommentReplyEditor").find("button[type=submit]").click()
    cy.wait("@CreateInlineCommentReply")

    // modify, submit
    cy.dataCy("inlineComment").eq(1).find("[data-cy=showRepliesButton]").click()
    cy.dataCy("inlineCommentReply").last().find("[data-cy=commentActions]").click()
    cy.dataCy("modifyComment").click()
    cy.dataCy("modifyInlineCommentReplyEditor").type("This is a modified inline comment reply.")
    cy.dataCy("modifyInlineCommentReplyEditor").find("button[type=submit]").click()

    // verify comment includes "This is a modified inline comment reply."
    cy.dataCy("inlineCommentReply").last().contains("This is a modified inline comment reply.")
  })

  it("should not display comment modify options for comments that a user did not create", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/100/review")

    // attempt to modify an overall comment from a random user
    cy.dataCy("overallComment").first().find("[data-cy=commentActions]").click()
    cy.dataCy("modifyComment").should("not.exist")

    // attempt to modify an inline comment from the publication editor
    cy.dataCy("toggleInlineCommentsButton").click()
    cy.dataCy("inlineComment").eq(1).find("[data-cy=commentActions]").click()
    cy.dataCy("modifyComment").should("not.exist")
  })

  it("enables access to the Submission Export page under the correct conditions", () => {
    cy.task("resetDb")
    cy.login({ email: "regularuser@meshresearch.net" })
    // Under Review
    cy.visit("submission/100/review")
    cy.dataCy("submission_export_btn").should("have.class","cursor-not-allowed")
    // Rejected
    cy.visit("submission/102/review")
    cy.dataCy("submission_export_btn").should("not.have.class","cursor-not-allowed")
  })

  it("scrolls when clicking on a footnote reference", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("submission/112/review")
    cy.injectAxe()
    cy.get("#fnref1").should((footnote) => {
      let scrollY = footnote[0].getBoundingClientRect().top
      // The position of the view should start above the first reference
      expect(scrollY).to.be.greaterThan(-1)
    })
    cy.get("#fnref1").click()
    cy.get("#fnref1").should((footnote) => {
      let scrollY = footnote[0].getBoundingClientRect().top
      // Now that the reference was clicked, the view should be below the first reference
      expect(scrollY).to.be.lessThan(0)
    })
    cy.get("a[href='#fnref1']").click()
    cy.get("#fnref1").should((footnote) => {
      let scrollY = footnote[0].getBoundingClientRect().top
      // Now that the footnote was clicked, the view should be above the first reference
      expect(scrollY).to.be.greaterThan(-1)
    })
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("does not show status changing options for reviewers", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewer@meshresearch.net" })
    cy.visit("submission/108/review")
    cy.dataCy("status-dropdown").should("not.exist")
  })

  it("shows the correct status change options for submissions marked as ACCEPTED_AS_FINAL", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@meshresearch.net" })
    cy.visit("submission/105/review")
    cy.dataCy("submission_status").contains("Accepted as Final")
    cy.dataCy("status-dropdown").click()
    cy.dataCy("archive")
    cy.dataCy("delete")
  })

  it("allows the status of a submission in ACCEPTED_AS_FINAL status to be changed to ARCHIVED and that status options are visible", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@meshresearch.net" })
    cy.visit("submission/105/review")
    cy.dataCy("submission_status").contains("Accepted as Final")
    cy.dataCy("status-dropdown").click()
    cy.dataCy("archive").click()
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.visit("submission/105/review")
    cy.dataCy("submission_status").contains("Archived")
    cy.dataCy("status-dropdown").click()
    cy.dataCy("decision_options").click()
    cy.dataCy("delete")
  })

  it("allows the status of a submission in ACCEPTED_AS_FINAL status to be changed to DELETED and that status options are NOT visible", () => {
    cy.task("resetDb")
    cy.login({ email: "reviewcoordinator@meshresearch.net" })
    cy.visit("submission/105/review")
    cy.dataCy("submission_status").contains("Accepted as Final")
    cy.dataCy("status-dropdown").click()
    cy.dataCy("delete").click()
    cy.dataCy("dirtyYesChangeStatus").click()
    cy.visit("submission/105/review")
    cy.dataCy("submission_status").contains("Deleted")
    cy.dataCy("status-dropdown").should('not.exist')
  })
})

/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />

import { a11yLogViolations } from '../../support/helpers'

describe("Publication Setup", () => {
  beforeEach(() => {
    cy.task("resetDb")
  })

  it("allows access based on role", () => {
    cy.login({ email: "publicationadministrator@meshresearch.net" })
    cy.visit("/publication/1/setup/basic")
    cy.url().should("not.include", "/error403")
  })

  it("restricts access based on role", () => {
    cy.login({ email: "publicationadministrator@meshresearch.net" })
    cy.visit("/publication/2/setup/basic")
    cy.url().should("include", "/error403")
  })

  it("should assert the Publication Details page is accessible", () => {
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("/publication/1")
    cy.injectAxe()
    cy.dataCy("publication_details_heading")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should show and hide the configuration button", () => {
    cy.login({ email: "publicationadministrator@meshresearch.net" })
    cy.visit("publication/1")
    cy.dataCy('configure_button')
    cy.visit("publication/2")
    cy.dataCy('configure_button').should('not.exist')
  })

  it("should allow assignment of administrators by application administrators", () => {
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("publication/1/setup/users")
    cy.interceptGQLOperation('UpdatePublicationAdmins')


    cy.dataCy("admins_list").within(() => {
      cy.userSearch('input_user', 'applicationAd')
      cy.qSelectItems('input_user').eq(0).click()
      cy.dataCy('button-assign').click();
    })
    cy.wait("@UpdatePublicationAdmins")
    cy.dataCy("admins_list").find(".q-list").contains("Application Administrator")

    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })


  it("should allow assignment of editors by application administrators", () => {
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("publication/1/setup/users")
    cy.interceptGQLOperation('UpdatePublicationEditors')

    cy.dataCy("editors_list").within(() => {
      cy.userSearch('input_user', 'applicationAd')
      cy.qSelectItems('input_user').eq(0).click()
      cy.dataCy('button-assign').click();
    })

    cy.wait("@UpdatePublicationEditors")
    cy.dataCy("editors_list").find(".q-list").contains("Application Administrator")

    cy.injectAxe()
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow removal of admins by application administrators", () => {
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("publication/1/setup/users")

    cy.intercept("/graphql").as("graphql")
    cy.interceptGQLOperation("UpdatePublicationAdmins")
    cy.dataCy("admins_list")
        .find('.q-list')
        .eq(0)
        .findCy("button_unassign")
        .click();
    cy.wait("@UpdatePublicationAdmins")

    cy.dataCy("admins_list").find(".q-list").should("not.exist")

  })


  it("should allow removal of editors by application administrators", () => {
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("publication/1/setup/users")

    cy.interceptGQLOperation("UpdatePublicationEditors")
    cy.dataCy("editors_list")
        .find('.q-list')
        .eq(0)
        .findCy("button_unassign")
        .click();
    cy.wait("@UpdatePublicationEditors")

    cy.dataCy("editors_list").find(".q-list").should("not.exist")

  })

  it("should allow editing of style criteria", () => {
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("publication/1/setup/criteria")
    cy.injectAxe()

    //Click edit on item 1
    cy.dataCy('editBtn').first().click()
    cy.checkA11y({
      exclude: [['[data-cy="description-input"']], //TODO: Restore this check once quasar #13275 is closed
      },
      null,
      a11yLogViolations
    )

    //Edit the criteria
    cy.dataCy('name-input').type(' Update')
    cy.dataCy('description-input').type("{selectAll}Updated description.")
    cy.dataCy("icon-button").click()

    //Change the icon and save the resulting new icon name
    cy.get('.q-icon-picker__container button:first', {timeout: 10000}).then(($button) => {
      const newIconName = $button.find('i').text()
      expect(newIconName).to.not.be.empty
      cy.wrap($button).click()
      cy.dataCy("button_save").click()

      //Wait for the form to go away
      cy.get('form[.data-cy="listItem"]:first').should('not.exist')
      cy.dataCy("listItem").first().contains("Accessibility Update")
      cy.dataCy("listItem").first().contains("Updated description")
      cy.dataCy("listItem").first().contains(newIconName)
    })
  })

  it("should allow adding a style criteria", () => {
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("publication/1/setup/criteria")

    //Check existing number of items:
    cy.dataCy('listItem').should('have.length', 4)

    //Create new item
    cy.dataCy('add-criteria-button').click()
    cy.dataCy('name-input').type('New Criteria')
    cy.dataCy('description-input').type("New criteria description.")
    cy.dataCy("button_save").click()

    //Wait for the form to go away
    cy.get('form[data-cy="listItem"]').should('not.exist')

    //Check a new item exists
    cy.dataCy('listItem').should('have.length', 5)
    cy.dataCy("listItem").last().contains("New Criteria")
    cy.dataCy("listItem").last().contains("New criteria description")
    cy.dataCy("listItem").last().contains('task_alt')
  })

  it("should allow deleting a style criteria", () => {
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("publication/1/setup/criteria")
    //Check existing number of items:
    cy.dataCy('listItem').should('have.length', 4)

    cy.dataCy('editBtn').first().click()
    cy.dataCy('button-delete').click()

    cy.get('.q-dialog .q-btn').last().click()

    cy.dataCy('listItem').should('have.length', 3)
  });

  it.only("should allow editing basic settings", () => {
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("publication/1/setup/basic")

    cy.dataCy('name_field').type(" Update")
    cy.dataCy('visibility_field').find('button:last').click()
    cy.dataCy('allow_submissions_field').find('button:last').should('have.attr', 'aria-pressed', 'false')
    cy.dataCy('allow_submissions_field').find('button:first').should('have.attr', 'aria-pressed', 'true')
    cy.dataCy('allow_submissions_field').find('button:last').click()
    cy.dataCy('allow_submissions_field').find('button:last').should('have.attr', 'aria-pressed', 'true')
    cy.dataCy('allow_submissions_field').find('button:first').should('have.attr', 'aria-pressed', 'false')
    cy.dataCy('button_save').click()
    cy.dataCy('button_saved').contains('Saved')
    // verify the 2nd publication has is closed for submissions by default
    cy.visit("publication/1/setup/basic")
    cy.dataCy('allow_submissions_field').find('button:last').should('have.attr', 'aria-pressed', 'true')
    cy.dataCy('allow_submissions_field').find('button:first').should('have.attr', 'aria-pressed', 'false')
    cy.injectAxe()
    cy.checkA11y(
      null,
      null,
      a11yLogViolations
    )
  })

  it("should allow editing content blocks", () => {
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("publication/1/setup/content")

    cy.dataCy('content_block_select').click()
    cy.qSelectItems('content_block_select').eq(0).click()
    cy.injectAxe()

    cy.checkA11y({
      exclude: [['[data-cy="content_field"']], //TODO: Restore this check once quasar #13275 is closed
      },
      null,
      a11yLogViolations
    )
    cy.dataCy('content_field').type("More description.")
    cy.dataCy('button_save').click()
    cy.dataCy('button_saved').contains('Saved')
  })
})

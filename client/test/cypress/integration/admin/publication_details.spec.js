/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />

import "cypress-axe"
import { a11yLogViolations } from '../../support/helpers'

describe("Publication Details", () => {
  beforeEach(() => {
    cy.task("resetDb")
  })

  it("restricts access based on role", () => {
    cy.login({ email: "regularuser@ccrproject.dev" })
    cy.visit("/publication/1")
    cy.url().should("include", "/error403")
  })

  it("allows access based on role", () => {
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("/publication/1")
    cy.url().should("not.include", "/error403")
  })

  it("should assert the Publication Details page is accessible", () => {
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("/publication/1")
    cy.injectAxe()
    cy.dataCy("publication_details_heading")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow assignments of editors and reject assignments of duplicate editors", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("/publication/1")
    cy.injectAxe()

    cy.dataCy("addEditorButton").click()
    // Initial Assignment
    cy.dataCy("input_editor_assignee").type("applicationAd")
    cy.qSelectItems("input_editor_assignee").eq(0).click()
    cy.dataCy("input_editor_assignee").prev('.q-chip').contains("applicationAdminUser")
    cy.dataCy("button_assign_editor").click()

    cy.dataCy("publication_details_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")

    cy.dataCy("list_assigned_editors").contains("Application Administrator")
    cy.dataCy("button_dismiss_notify").click()

    // Duplicate Assignment
    cy.dataCy("input_editor_assignee").type("applicationAd")
    cy.qSelectItems("input_editor_assignee").eq(0).click()
    cy.dataCy("input_editor_assignee").prev('.q-chip').contains("applicationAdminUser")
    cy.dataCy("button_assign_editor").click()

    cy.dataCy("publication_details_notify")
      .should("be.visible")
      .should("have.class", "bg-negative")

    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("should allow editing of style criteria", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("/publication/1")
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
      cy.get('form[data-cy="listItem"]:first').should('not.exist')
      cy.dataCy("listItem").first().contains("Accessibility Update")
      cy.dataCy("listItem").first().contains("Updated description")
      cy.dataCy("listItem").first().contains(newIconName)
    })
  })

  it("should allow adding a style criteria", () => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("/publication/1")

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
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@ccrproject.dev" })
    cy.visit("/publication/1")
    //Check existing number of items:
    cy.dataCy('listItem').should('have.length', 4)

    cy.dataCy('editBtn').first().click()
    cy.dataCy('button-delete').click()

    cy.get('.q-dialog .q-btn').last().click()

    cy.dataCy('listItem').should('have.length', 3)
  });
})

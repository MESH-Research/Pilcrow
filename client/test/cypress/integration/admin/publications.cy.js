/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />

import { a11yLogViolations } from '../../support/helpers'

describe("Admin Publications", () => {
  beforeEach(() => {
    cy.task("resetDb")
    cy.login({ email: "applicationadministrator@meshresearch.net" })
    cy.visit("/admin/publications")
    cy.injectAxe()
  })

  it("creates new publications and navigates to setup page", () => {

    cy.dataCy("create_pub_button").click()
    cy.checkA11y(null, null, a11yLogViolations)
    cy.dataCy("new_publication_input").type("Publication from Cypress{enter}")
    cy.dataCy("create_publication_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")
    cy.url().should('match', /publication\/[0-9]+\/setup\/basic$/)
  })

  it("prevents publication creation when the name is empty", () => {
    cy.dataCy("create_pub_button").click()
    cy.dataCy("new_publication_input").type("{enter}")
    cy.dataCy("publications_list")
    cy.dataCy("name_field_error").should("be.visible")
    cy.dataCy("new_publication_input").type("Draft Publication from Cypress")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("prevents publication creation when the name exceeds the maximum length", () => {
    cy.dataCy("create_pub_button").click()
    const name_257_characters = "".padEnd(257, "01234567890")
    cy.dataCy("new_publication_input").type(name_257_characters, {
      delay: 0,
    })
    cy.dataCy("name_field_error").should("be.visible")
    cy.get(".q-transition--field-message-leave-active").should("not.exist")
    cy.get(".q-notification--top-enter-active").should("not.exist")
    cy.checkA11y(null, null, a11yLogViolations)
  })

  it("prevents publication creation when the name is not unique", () => {
    cy.dataCy("create_pub_button").click()
    cy.dataCy("new_publication_input").type(
      "Duplicate Publication from Cypress{enter}"
    )
    cy.dataCy("create_publication_notify")
      .should("be.visible")
      .should("have.class", "bg-positive")
    cy.visit('/admin/publications')
    cy.dataCy("create_pub_button").click()
    cy.dataCy("new_publication_input").type(
      "Duplicate Publication from Cypress{enter}"
    )
    cy.dataCy("name_field_error").should("be.visible")
  })
})

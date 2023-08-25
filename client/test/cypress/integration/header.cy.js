/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />


describe("Header", () => {
  beforeEach(() => {
    cy.task("resetDb")
  })

  it("should have a login and register link in header", () => {
    cy.visit('/')
    cy.get("header").within(() => {
      cy.contains("Login").should("have.attr", "href", "/login")
      cy.contains("Register").should("have.attr", "href", "/register")
    })

    cy.login({ email: "regularuser@pilcrow.dev" })
    cy.reload()

    cy.get("header").contains("regularUser").click()

    cy.dataCy("headerUserMenu").within(() => {
      cy.contains("Login and Password").should("have.attr", "href", "/account/profile")
      cy.contains("Logout").click()
    })

    cy.get("header").within(() => {
      cy.contains("Login")
      cy.contains("Register")
    })

    cy.visit("/")

    cy.get("header").within(() => {
      cy.contains("Login")
      cy.contains("Register")
    })
  })

  it("should not logout when page changes are protected", () => {
    cy.login({ email: "regularuser@pilcrow.dev" })
    cy.visit("/account/metadata")
    cy.dataCy("facebook").type("myface")

    cy.get("header").within(() => {
      cy.contains("regularUser").click()
    })

    cy.dataCy("headerUserMenu").within(() => {
      cy.contains("Logout").click()
    })
    cy.dataCy('dirtyKeepChanges').click()

    cy.url().should('include', '/account/metadata')

    cy.dataCy("headerUserMenu").within(() => {
      cy.contains("Logout").click()
    })

    cy.dataCy('dirtyDiscardChanges').click()
    cy.url().should('eq', Cypress.config().baseUrl + "/")

    cy.get("header").within(() => {
      cy.contains("Login")
    })
  })
})

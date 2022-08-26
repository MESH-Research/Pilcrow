/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

// Use `cy.dataCy` custom command for more robust tests
// See https://docs.cypress.io/guides/references/best-practices.html#Selecting-Elements

// ** This file is an example of how to write Cypress tests, you can safely delete it **

// This test will pass when run against a clean Quasar project
import "cypress-axe"
import { a11yLogViolations } from '../support/helpers'

describe("Landing", () => {
  beforeEach(() => {
    cy.visit("/")
  })
  it("should assert that <title> is correct", () => {
    cy.title().should("include", "CCR")
  })

  it("should assert home page is accessible", () => {
    // Inject the axe-core libraray
    cy.injectAxe()
    //Wait for page to be ready
    cy.dataCy("vueIndex")
    // check the page for a11y errors
    cy.checkA11y(null, null, a11yLogViolations)
  })
})
describe("Home page tests", () => {
  beforeEach(() => {
    cy.visit("/")
  })

  it("has working login link", () => {
    cy.contains("Login").click()

    cy.url().should("include", "/login")
  })

  it("has working register link", () => {
    cy.contains("Register").click()
    cy.url().should("include", "/register")
  })
})

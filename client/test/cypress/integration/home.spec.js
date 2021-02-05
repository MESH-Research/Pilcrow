/// <reference path="cypress" />
/// <reference path="../support/index.d.ts" />

// Use `cy.dataCy` custom command for more robust tests
// See https://docs.cypress.io/guides/references/best-practices.html#Selecting-Elements

// ** This file is an example of how to write Cypress tests, you can safely delete it **

// This test will pass when run against a clean Quasar project
describe("Landing", () => {
  beforeEach(() => {
    cy.visit("/");
  });
  it(".should() - assert that <title> is correct", () => {
    cy.title().should("include", "CCR");
  });
});
describe("Home page tests", () => {
  beforeEach(() => {
    cy.visit("/");
  });

  it("has working login link", () => {
    cy.contains("Login").click();

    cy.url().should("include", "/login");
  });

  it("has working register link", () => {
    cy.contains("Register").click();
    cy.url().should("include", "/register");
  });
});



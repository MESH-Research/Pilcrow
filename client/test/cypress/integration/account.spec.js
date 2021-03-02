/// <reference path="cypress" />
/// <reference path="../support/index.d.ts" />

describe("Account", () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login({ email: "regularuser@ccrproject.dev" });
    cy.visit('/');
  });

  it("can visit the account profile page from the dropdown", () => {
    cy.dataCy("dropdown_username").click();
    cy.dataCy("link_my_account").click();
    cy.url().should("include", "/account/profile");
  });

  it("has authenticated after registering", () => {
    cy.url().should("include", "/account/profile");
  });

});

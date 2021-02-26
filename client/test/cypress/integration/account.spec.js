/// <reference path="cypress" />
/// <reference path="../support/index.d.ts" />

describe("Account", () => {
  beforeEach(() => {
    cy.visit("/");
    cy.dataCy("link_register").click();
    cy.dataCy("register_email").type("testemail2@email.com");
    cy.dataCy("register_username").type("testusername2");
    cy.dataCy("password").type("Ek-]'&7wD9,7&p{z");
    cy.dataCy("button_register").click();

    cy.dataCy("login_email").type("testemail2@email.com");
    cy.dataCy("password").type("Ek-]'&7wD9,7&p{z");
    cy.dataCy("button_login").click();

    cy.dataCy("dropdown_username").click();
    cy.dataCy("link_my_account").click();
  });

  it("has authenticated after registering", () => {
    cy.url().should("include", "/account/profile");
  });

});

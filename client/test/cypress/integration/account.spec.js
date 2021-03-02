/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

describe('Account', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login({ email: 'regularuser@ccrproject.dev' });
    cy.visit('/');
  });

  it('can visit the account profile page from the dropdown', () => {
    cy.dataCy('dropdown_username').click();
    cy.dataCy('link_my_account').click();
    cy.url().should('include', '/account/profile');
  });

  it('can update the name field', () => {
    cy.visit('/account/profile');
    cy.dataCy('update_user_name').clear().type('Updated User');
    cy.dataCy('update_user_button_save').click();
    cy.dataCy('avatar_name').contains('Updated User');
  });

  it('can update the username field', () => {
    cy.visit('/account/profile');
    cy.dataCy('update_user_username').clear().type('updatedUser');
    cy.dataCy('update_user_button_save').click();
    cy.dataCy('avatar_username').contains('updatedUser');
  });

});

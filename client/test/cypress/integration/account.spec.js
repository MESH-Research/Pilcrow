/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import 'cypress-axe';

describe('Account', () => {
  beforeEach(() => {
    cy.task('resetDb');
  });

  it('can update the name field', () => {
    cy.login({ email: 'regularuser@ccrproject.dev' });
    cy.visit('/account/profile');
    cy.dataCy('update_user_name').clear().type('Updated User');
    cy.dataCy('update_user_button_save').click();
    cy.dataCy('avatar_name').contains('Updated User');
  });

  it('can update the username field', () => {
    cy.login({ email: 'regularuser@ccrproject.dev' });
    cy.visit('/account/profile');
    cy.dataCy('update_user_username').clear().type('updatedUser');
    cy.dataCy('update_user_button_save').click();
    cy.dataCy('avatar_username').contains('updatedUser');
  });

  it('can update the password field', () => {
    cy.login({ email: 'regularuser@ccrproject.dev' });
    cy.visit('/account/profile');
    cy.dataCy('update_user_password').clear().type('XMYeygtC7TuxgER4');
    cy.dataCy('update_user_button_save').click();
    cy.dataCy('update_user_notify').should('be.visible').should('have.class','bg-positive');
  });

  it('should assert the page is accessible', () => {
    // Inject the axe-core libraray
    cy.injectAxe();
    cy.checkA11y();
  });

  // TODO: Uncomment once email updates work again
  // it('can update the email field', () => {
  //   cy.dataCy('update_user_email').clear().type('updateduser@ccrproject.dev');
  //   cy.dataCy('update_user_button_save').click();
  //   cy.dataCy('update_user_notify').should('be.visible').should('have.class','bg-positive');
  // });

});

/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />

import 'cypress-axe';

describe('Publications', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login({ email: "applicationadministrator@ccrproject.dev" });
    cy.visit('/admin/publications');
  });

  it('should assert the initial load of the page is accessible', () => {
    cy.injectAxe();
    cy.dataCy('create_new_publication_form');
    cy.checkA11y();
  });

  it('creates new publications and updates the publications list', () => {
    cy.dataCy('new_publication_input')
      .type('Publication from Cypress{enter}');
    cy.dataCy('publications_list').contains('Publication from Cypress');
    cy.dataCy('create_publication_notify').should('be.visible').should('have.class','bg-positive');
    cy.injectAxe();
    cy.dataCy('new_publication_input')
      .type('Draft Publication from Cypress');
    cy.checkA11y();
  });

  // it('prevents publication creation when the name is empty', () => {

  // });

  // it('prevents publication creation when the name is not unique', () => {

  // });

  // it('prevents publication creation when the name is exceeds the maximum length', () => {

  // });

});

/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />

import 'cypress-axe';

describe('Publications', () => {
  it('creates new publications and checks the publications page', () => {
    cy.task('resetDb');
    cy.login({ email: "applicationadministrator@ccrproject.dev" });
    cy.visit('admin/publications');
    cy.dataCy('new_publication_input')
      .type('Publication from Cypress{enter}');
    cy.dataCy('publications_list').contains('Publication from Cypress');
    cy.visit('/publications');
    cy.injectAxe();
    cy.dataCy('publications_list').contains('Publication from Cypress');
    cy.checkA11y();
  });

  it('excludes the publications from the list when they are not publicly visible', () =>{ 
    cy.login({ email: "regularuser@ccrproject.dev" });
    cy.visit('/publications');
    cy.injectAxe();
    cy.dataCy('publications_list').should('be.empty');
    cy.checkA11y();

  });
});



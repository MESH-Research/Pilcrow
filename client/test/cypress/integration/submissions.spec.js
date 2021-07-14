/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import 'cypress-axe';

describe('Submissions', () => {
  it('creates new submissions', () => {
    cy.task('resetDb');
    cy.login({ email: "applicationadministrator@ccrproject.dev" });
    cy.visit('submissions');
    cy.injectAxe();
    cy.dataCy('new_submission_title_input')
      .type('Submission from Cypress{enter}');
    cy.dataCy('new_submission_publication_input')
      .click();
    cy.get('.publication_options')
      .contains('CCR Test Publication 1')
      .click();
    cy.dataCy('save_submission')
      .click();
    cy.dataCy('submissions_list')
      .contains('Submission from Cypress');
    cy.dataCy('create_submission_notify').should('be.visible').should('have.class','bg-positive');
    cy.get('.q-notification--top-enter-active').should('not.exist');
    cy.checkA11y(null, {
      rules: {
          'nested-interactive': { enabled: false },
      },
    });
  });

});



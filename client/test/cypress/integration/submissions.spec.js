/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import 'cypress-axe';

describe('Submissions', () => {
  it('creates new submissions', () => {
    cy.task('resetDb');
    cy.login({ email: "applicationadministrator@ccrproject.dev" });
    cy.visit('submissions');
    cy.dataCy('new_submission_title_input')
      .type('Submission from Cypress{enter}');
    cy.dataCy('new_submission_publication_input')
      .click();
    cy.get('.q-virtual-scroll__content')
      .contains('Collaborative Review Organization')
      .click();
    cy.dataCy('save_submission')
      .click();
    cy.injectAxe();
    cy.dataCy('submissions_list')
      .contains('Submission from Cypress');
    cy.dataCy('create_submission_notify').should('be.visible').should('have.class','bg-positive');
    cy.checkA11y(null, {
      rules: {
          'nested-interactive': { enabled: false },
      },
    });
  });

});



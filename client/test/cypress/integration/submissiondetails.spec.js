/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import 'cypress-axe';

describe('Submissions Details', () => {
  it('should assert the Submission Details page is accessible', () => {
    cy.task('resetDb');
    cy.login({ email: "applicationadministrator@ccrproject.dev" });
    cy.visit('submission/100');
    cy.injectAxe();
    cy.dataCy('assignedReviewersList')
    cy.checkA11y();
  });

  it('should allow selections of assigned reviewers within the search input', () => {
    cy.task('resetDb');
    cy.login({ email: "applicationadministrator@ccrproject.dev" });
    cy.visit('submission/100');
    cy.injectAxe();
    cy.get('#review_assignee_input')
      .type('applicationAdminUser')
    cy.dataCy('review_assignee_result')
      .click()
    cy.dataCy('review_assignee_selected').contains('applicationAdminUser')
    cy.get('#review_assignee_input')
      .type('test{backspace}{backspace}{backspace}{backspace}')
    cy.checkA11y(null, {
      rules: {
          'region': { enabled: false },
      },
    });
  })

});



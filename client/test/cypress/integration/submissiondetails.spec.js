/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import 'cypress-axe';

describe('Submissions Details', () => {
  it('should assert the Submission Details page is accessible', () => {
    cy.task('resetDb');
    cy.login({ email: "applicationadministrator@ccrproject.dev" });
    cy.visit('submission/1');
    cy.injectAxe();
    cy.dataCy('assignedReviewersList')
    cy.checkA11y();
  });

});



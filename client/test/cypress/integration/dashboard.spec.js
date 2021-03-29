/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

import 'cypress-axe';

describe("Dashboard", () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login({ email: 'regularuser@ccrproject.dev' });
  });
  it('should assert the page is accessible', () => {
    cy.visit('/dashboard');
    cy.injectAxe();
    //Wait for the page to be loaded.
    cy.dataCy('vueDashboard');
    cy.checkA11y();
  });
});

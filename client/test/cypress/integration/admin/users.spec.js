/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />
// Use `cy.dataCy` custom command for more robust tests
// See https://docs.cypress.io/guides/references/best-practices.html#Selecting-Elements

import 'cypress-axe';

describe('Admin Users Index', () => {
  beforeEach(() => {
    cy.task('resetDb');
  });

  it('restricts access based on role', () => {
    cy.login({ email: "regularuser@ccrproject.dev" });
    cy.visit('/admin/users');
    cy.url().should('include', '/error403');
  });
  it('allows access based on role and check for a11y issues', () => {
    cy.login({ email: "applicationadministrator@ccrproject.dev" });
    cy.visit('/admin/users');
    cy.url().should('not.include', '/error403');
    // Inject the axe-core libraray
    cy.injectAxe();
    //cy.contains('admin users index');
    cy.checkA11y();
  });

});

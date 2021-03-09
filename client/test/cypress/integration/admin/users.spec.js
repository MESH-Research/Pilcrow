/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />

describe('Admin Users Index', () => {
  beforeEach(() => {
    cy.task('resetDb');
  });

  it('restricts access based on role', () => {
    cy.login({ email: "regularuser@ccrproject.dev" });
    cy.visit('/admin/users');
    cy.url().should('include', '/error403');

    cy.login({ email: "adminuser@ccrproject.dev" });
    cy.visit('/admin/users');
    cy.url().should('not.include', '/error403');
    cy.contains('admin users index');
  });


});

/// <reference types="Cypress" />
/// <reference path="../../support/index.d.ts" />

import 'cypress-axe';

describe('Publications', () => {
  beforeEach(() => {
    cy.task('resetDb');
    cy.login({ email: "applicationadministrator@ccrproject.dev" });
    cy.visit('/admin/publications');
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

  it('prevents publication creation when the name is empty', () => {
    cy.dataCy('new_publication_input')
      .type('{enter}');
    cy.dataCy('publications_list');
    cy.dataCy('create_publication_notify').should('be.visible').should('have.class','bg-negative');
    cy.injectAxe();
    cy.dataCy('new_publication_input')
      .type('Draft Publication from Cypress');
    cy.checkA11y();
  });

  it('prevents publication creation when the name exceeds the maximum length', () => {
    const name_256_characters = '0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345';
    cy.dataCy('new_publication_input')
      .type(name_256_characters + '{enter}');
    cy.dataCy('banner_form_error').should('be.visible').should('have.class','bg-negative');
    cy.injectAxe();
    cy.checkA11y();
  });

  it('prevents publication creation when the name is not unique', () => {
    cy.dataCy('new_publication_input')
      .type('Duplicate Publication from Cypress{enter}');
    cy.dataCy('create_publication_notify').should('be.visible').should('have.class','bg-positive');
    cy.injectAxe();
    cy.dataCy('publications_list').contains('Duplicate Publication from Cypress');
    cy.dataCy('new_publication_input')
      .type('Duplicate Publication from Cypress{enter}');
    cy.dataCy('banner_form_error').should('be.visible').should('have.class','bg-negative');
    cy.checkA11y();
  });

  it('should assert the initial load of the page is accessible', () => {
    cy.injectAxe();
    cy.dataCy('create_new_publication_form');
    cy.checkA11y();
  });

});

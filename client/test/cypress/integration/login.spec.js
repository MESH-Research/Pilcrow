/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

describe('login page', () => {
    beforeEach(() => {
        cy.task('resetDb');
    });

    it('allows a user to login', () => {
        cy.visit('/login');
        cy.get('.q-form').within(() => {
            cy.dataCy('emailField').type('regularuser@ccrproject.dev');
            cy.dataCy('passwordField').type('regularPassword!@#');
        });
        cy.contains('Login').click();
    });

    it('validates fields', () => {

    });

    it('redirects to login when requesting a protected page', () => {
        
    });
});
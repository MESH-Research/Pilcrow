/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />

describe('login page', () => {
    beforeEach(() => {
        cy.task('resetDb');
    });

    it('allows a user to login', () => {
        cy.visit('/login');
        cy.get('.q-form').within(() => {
            cy.dataCy('email_field').type('regularuser@ccrproject.dev');
            cy.dataCy('password_field').type('regularPassword!@#');
            cy.get('.q-card__actions').contains('Login').click();
            cy.url().should('include', '/dashboard');
        });
    });

    it('validates fields and displays errors', () => {
        cy.visit('/login');
        cy.get('.q-form').within(() => {
            cy.dataCy('email_field')
                .type('{enter}')
                .parents('label')
                .should('have.class', 'q-field--error')
            cy.dataCy('email_field').type('regularuser@ccrproject.dev{enter}');

            cy.dataCy('password_field')
                .parents('label')
                .should('have.class', 'q-field--error')
                .type('somePass{enter}');
            
            cy.dataCy('authFailureMessages')
                .should('be.visible')
                .contains('invalid credentials');
        });
    });

    it('redirects to login when requesting a protected page', () => {
        cy.visit('/account/profile');
        cy.url().should('include', '/login');
        cy.get('[role="alert"]').contains('login to access that page');

        cy.get('.q-form').within(() => {
            cy.dataCy('email_field').type('regularuser@ccrproject.dev');
            cy.dataCy('password_field').type('regularPassword!@#{enter}');
            cy.url().should('include', '/account/profile');
        })
    });
});

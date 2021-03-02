/// <reference types="Cypress" />
/// <reference path="../support/index.d.ts" />


describe("Register", () => {
    beforeEach(() => {
        cy.task('resetDb');
        cy.visit("/register");
    });

    it("validates fields", () => {
        cy.get('.q-form').within(() => {
            cy.dataCy('email_field')
                .type('{enter}')
                .parents('label')
                .should('have.class', 'q-field--error')
            cy.dataCy('email_field')
                .type('regularuser@ccrproject.dev{enter}')
                .parents('label')
                .should('have.class', 'q-field--error')
                .contains('already registered');
            
            cy.dataCy('email_field')
                .clear()
                .type('ccrproject{enter}')
                .parents('label')
                .should('have.class', 'q-field--error')
                .contains("a valid email");
            
            cy.dataCy('email_field')
                .type('@ccrproject.dev{enter}')
                .parents('label')
                .should('not.have.class', 'q-field--error');
            
            
            cy.dataCy('username_field')
                .parents('label')
                .should('have.class', 'q-field--error')
                .contains('is required');
            
            cy.dataCy('username_field')
                .type('regularUser{enter}')
                .parents('label')
                .should('have.class', 'q-field--error')
                .contains('is not available');

            cy.dataCy('username_field')
                .type('newUser{enter}')
                .parents('label')
                .should('not.have.class', 'q-field--error');
            
            cy.dataCy('password_field')
                .parents('label')
                .should('have.class', 'q-field--error')
                .contains('is required');
            
            cy.dataCy('password_field')
                .type('password')
                .parents('label')
                .should('have.class', 'q-field--error')
                .contains('be more complex');
            
            cy.dataCy('password_field')
                .type('!@#$#@password')
                .parents('label')
                .should('not.have.class', 'q-field--error')
            
            cy.get('[type="submit"]').click();

            cy.url().should('include', '/dashboard');


        });
    });

    it('submits with enter key', () => {
        cy.get('.q-form').within(() => {
            cy.dataCy('username_field')
                .type('newUserName');
            cy.dataCy('email_field')
                .type('newEmail@ccrproject.dev');
            cy.dataCy('password_field')
                .type('password_field!@#12{enter}');
        });

        cy.url().should('include', '/dashboard');
    })




});
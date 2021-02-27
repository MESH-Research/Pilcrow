/// <reference path="cypress" />
/// <reference path="../support/index.d.ts" />

describe('Header', () => {
    beforeEach(() => {
        cy.task('resetDb');
        cy.visit('/');
    })

    it("should have a login and register link in header", () => {
        cy.get('header').within(() => {
            cy.contains('Login').should('have.attr', 'href', '/login');
            cy.contains('Register').should('have.attr', 'href', '/register');
        });

        cy.login({ email: "regularUser@ccrproject.dev" });
        cy.visit('/');

        cy.get('header').within(() => {
            cy.contains('regularUser').should('have.attr', 'href', '/dashboard');
            cy.contains('Logout');

            cy.logout();
            cy.visit('/');
            
            
            cy.contains('Login');
        });
        
    })


});
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

        cy.login({ email: "regularuser@ccrproject.dev" });
        cy.visit('/');

        cy.get('header').within(() => {
            cy.contains('regularUser').click();
        });
        cy.dataCy('headerUserMenu').contains('Logout').click();

        cy.get('header').within(() => {
            cy.contains('Login');
            cy.contains('Register');
        });

        cy.visit('/');

        cy.get('header').within(() => {
            cy.contains('Login');
            cy.contains('Register');
        });
    });
});
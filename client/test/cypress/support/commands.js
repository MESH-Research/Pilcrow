// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypressio/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This is will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })

/**
 * Custom command to select DOM element by data-cy attribute.
 *
 * see https://docs.cypress.io/guides/references/best-practices.html#Selecting-Elements
 *
 * @example cy.dataCy('greeting')
 */
Cypress.Commands.add("dataCy", value => {
  return cy.get(`[data-cy=${value}]`);
});

/**
 * Custom command to test being on a given route.
 * @example cy.testRoute('home')
 */
Cypress.Commands.add("testRoute", route => {
  cy.location().should(loc => {
    expect(loc.hash).to.contain(route);
  });
});

// these two commands let you persist local storage between tests
const LOCAL_STORAGE_MEMORY = {};

/**
 * Persist current local storage data.
 * @example cy.saveLocalStorage()
 */
Cypress.Commands.add("saveLocalStorage", () => {
  Object.keys(localStorage).forEach(key => {
    LOCAL_STORAGE_MEMORY[key] = localStorage[key];
  });
});

/**
 * Restore saved data to local storage.
 * @example cy.restoreLocalStorage()
 */
Cypress.Commands.add("restoreLocalStorage", () => {
  Object.keys(LOCAL_STORAGE_MEMORY).forEach(key => {
    localStorage.setItem(key, LOCAL_STORAGE_MEMORY[key]);
  });
});

/**
 * Get the root vue object.
 * @example cy.getVue()
 */
Cypress.Commands.add("getVue", () => {
  cy.get("#q-app").then($app => {
    cy.wrap($app.get(0).__vue__);
  });
});

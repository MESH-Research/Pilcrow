declare namespace Cypress {
  interface Chainable {
    /**
     * Custom command to select DOM element by data-cy attribute.
     * @example cy.dataCy('greeting')
     */
    dataCy<E extends Node = HTMLElement>(value: string): Chainable<JQuery<E>>;

    /**
     * Custom command to test being on a given route.
     * @example cy.testRoute('home')
     */
    testRoute(value: string): void;

    /**
     * Persist current local storage data.
     * @example cy.saveLocalStorage()
     */
    saveLocalStorage(): void;

    /**
     * Restore saved data to local storage.
     * @example cy.restoreLocalStorage()
     */
    restoreLocalStorage(): void;

    /**
     * Login a user without supplying credentials.
     * @example cy.login({email: "user@example.com"})
     */
    login(user: UserInput): UserObject;

    /**
     * Logout the user.
     */
    logout(): UserObject;

    /** Requests and returns a new xsrfToken
     * 
     * @example cy.xsrfToken().then((token) => {...})
     */
    xsrfToken(): string;

  }

  interface UserInput {
    email: string
  }

  interface UserObject {
    email: string
    name: string
    username: string
    id: number

  }
}

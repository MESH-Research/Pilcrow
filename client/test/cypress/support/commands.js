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
 * Login to the api without providing authentication.
 * 
 * @example cy.login({email: 'mytestuser@example.com'})
 */
Cypress.Commands.add('login', ({ email }) => {
  cy.xsrfToken().then((token) => {
    
    return cy.request(
      {
        method: 'POST',
        url: '/graphql',
        body: { query: `mutation { forceLogin(email: "${email}") {username, email, id, name}}` },
        headers: {
          origin: Cypress.config().baseUrl,
          'X-XSRF-TOKEN': token
        }
      })
    .then(response => {
      expect(response.status).to.eq(200)
      expect(response.body.data.forceLogin.email).to.eq(email)
      return response;
    });
  });
})

/**
 * Logout via the api.
 * 
 * @example cy.logout();
 */
Cypress.Commands.add('logout', () => {
  cy.xsrfToken().then((token) => {
    return cy.request({
      method: 'POST',
      url: '/graphql',
      body: { query: `mutation { logout() }` },
      headers: {
        origin: Cypress.config().baseUrl,
        'X-XSRF-TOKEN': token
      }
    })
    .then((response) => {
      expect(response.body.errors).toBeUndefined();
      return response.body.data.logout;
    });
  });
});

/**
 * Fetches and returns the XSRF token.
 * 
 * @example cy.csrfToken().then((token) => {...})
 */
Cypress.Commands.add('xsrfToken', () => {
  return cy.request({
    url: '/sanctum/csrf-cookie',
    headers: {
      origin: Cypress.config().baseUrl,
    }
  }).then((response) => {
    const cookie = response.headers['set-cookie']
        .filter(s => s.startsWith('XSRF-TOKEN'))
        .reduce(c => c);
    return decodeURIComponent(cookie.match(/XSRF-TOKEN=([^;]+)/)[1]);
  });
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

/**
 * Custom command to select DOM element by data-cy attribute.
 *
 * see https://docs.cypress.io/guides/references/best-practices.html#Selecting-Elements
 *
 * @example cy.dataCy('greeting')
 */
Cypress.Commands.add("dataCy", (value) => {
  return cy.get(`[data-cy=${value}]`)
})
Cypress.Commands.add(
  "findCy",
  {
    prevSubject: true
  },
  (subject, value) => {
    return subject.find(`[data-cy=${value}]`)
  }
)

Cypress.Commands.add("qSelect", (dataCyId) => {
  return cy.dataCy(dataCyId).closest(".q-select")
})

Cypress.Commands.add("qSelectItems", (value) => {
  return cy.get(`[data-cy=${value}]`).then((input) => {
    if (!input.is("input")) {
      input = input.find("input")
    }
    return cy
      .wrap(input)
      .invoke("attr", "id")
      .then((id) => {
        return cy.root().closest("html").find(`#${id}_lb`).find(".q-item")
      })
  })
})

Cypress.Commands.add("userSearch", (dataCy, searchTerm) => {
  cy.intercept("/graphql", (req) => {
    let operation
    if (Array.isArray(req.body)) {
      operation = req.body.find((op) => {
        return (
          Object.prototype.hasOwnProperty.call(op, "operationName") &&
          op.operationName == "SearchUsers" &&
          op.variables.term == searchTerm
        )
      })
    } else {
      if (
        Object.prototype.hasOwnProperty.call(req.body, "operationName") &&
        req.body.operationName == "SearchUsers" &&
        req.body.variables.term == searchTerm
      ) {
        operation = req.body
      }
    }
    if (operation) {
      req.alias = "searchResult"
      req.reply()
    } else {
      console.log(req)
    }
  })
  cy.dataCy(dataCy).type(searchTerm)
  cy.wait("@searchResult")
})

Cypress.Commands.add("interceptGQLOperation", (operationName) => {
  cy.intercept("/graphql", (req) => {
    let operation
    if (Array.isArray(req.body)) {
      operation = req.body.find((op) => {
        return (
          Object.prototype.hasOwnProperty.call(op, "operationName") &&
          op.operationName == operationName
        )
      })
    } else {
      if (
        Object.prototype.hasOwnProperty.call(req.body, "operation") &&
        req.body.operation == operationName
      ) {
        operation = req.body
      }
    }
    if (operation) {
      req.alias = operationName
      req.reply()
    }
  })
})

// Passwords for seeded test users (see backend/database/seeders/UserSeeder.php)
const SEEDED_PASSWORDS = {
  "applicationadministrator@meshresearch.net": "adminPassword!@#",
  "publicationadministrator@meshresearch.net": "publicationadminPassword!@#",
  "publicationeditor@meshresearch.net": "editorPassword!@#",
  "reviewcoordinator@meshresearch.net": "coordinatorPassword!@#",
  "reviewer@meshresearch.net": "reviewerPassword!@#",
  "regularuser@meshresearch.net": "regularPassword!@#"
}

/**
 * Login to the api using seeded credentials.
 *
 * @example cy.login({email: 'regularuser@meshresearch.net'})
 */
Cypress.Commands.add("login", ({ email }) => {
  const password = SEEDED_PASSWORDS[email.toLowerCase()]
  if (!password) {
    throw new Error(
      `No seeded password found for "${email}". Add it to SEEDED_PASSWORDS in commands.js.`
    )
  }
  cy.xsrfToken().then((token) => {
    return cy
      .request({
        method: "POST",
        url: "/graphql",
        body: {
          query: `mutation Login($email: String!, $password: String!) { login(email: $email, password: $password) { id, username, email, name } }`,
          variables: { email, password }
        },
        headers: {
          origin: Cypress.config().baseUrl,
          "X-XSRF-TOKEN": token
        }
      })
      .then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.data.login.email).to.eq(email)
        return response
      })
  })
})

/**
 * Logout via the api.
 *
 * @example cy.logout();
 */
Cypress.Commands.add("logout", () => {
  cy.xsrfToken().then((token) => {
    return cy
      .request({
        method: "POST",
        url: "/graphql",
        body: { query: `mutation { logout() }` },
        headers: {
          origin: Cypress.config().baseUrl,
          "X-XSRF-TOKEN": token
        }
      })
      .then((response) => {
        expect(response.body.errors).toBeUndefined()
        return response.body.data.logout
      })
  })
})

/**
 * Fetches and returns the XSRF token.
 *
 * @example cy.csrfToken().then((token) => {...})
 */
Cypress.Commands.add("xsrfToken", () => {
  return cy
    .request({
      url: "/sanctum/csrf-cookie",
      headers: {
        origin: Cypress.config().baseUrl
      }
    })
    .then((response) => {
      const cookie = response.headers["set-cookie"]
        .filter((s) => s.startsWith("XSRF-TOKEN"))
        .reduce((c) => c)
      return decodeURIComponent(cookie.match(/XSRF-TOKEN=([^;]+)/)[1])
    })
})

/**
 * Custom command to test being on a given route.
 * @example cy.testRoute('home')
 */
Cypress.Commands.add("testRoute", (route) => {
  cy.location().should((loc) => {
    expect(loc.hash).to.contain(route)
  })
})

// these two commands let you persist local storage between tests
const LOCAL_STORAGE_MEMORY = {}

/**
 * Persist current local storage data.
 * @example cy.saveLocalStorage()
 */
Cypress.Commands.add("saveLocalStorage", () => {
  Object.keys(localStorage).forEach((key) => {
    LOCAL_STORAGE_MEMORY[key] = localStorage[key]
  })
})

/**
 * Restore saved data to local storage.
 * @example cy.restoreLocalStorage()
 */
Cypress.Commands.add("restoreLocalStorage", () => {
  Object.keys(LOCAL_STORAGE_MEMORY).forEach((key) => {
    localStorage.setItem(key, LOCAL_STORAGE_MEMORY[key])
  })
})

/**
 * Get the root vue object.
 * @example cy.getVue()
 */
Cypress.Commands.add("getVue", () => {
  cy.get("#q-app").then(($app) => {
    cy.wrap($app.get(0).__vue__)
  })
})
//TODO: Remove this once the underlying issue is addressed in cypress or cypress axe.
// We're just overriding the timeout.
Cypress.Commands.overwrite("injectAxe", () => {
  var fileName =
    typeof (require === null || require === void 0
      ? void 0
      : require.resolve) === "function"
      ? require.resolve("axe-core/axe.min.js")
      : "node_modules/axe-core/axe.min.js"
  cy.readFile(fileName, { timeout: 20000 }).then(function (source) {
    return cy.window({ log: false }).then(function (window) {
      window.eval(source)
    })
  })
})

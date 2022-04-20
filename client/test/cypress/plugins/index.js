/* eslint-env node */
// ***********************************************************
// This example plugins/index.js can be used to load plugins
//
// You can change the location of this file or turn off loading
// the plugins file with the 'pluginsFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/plugins-guide
// ***********************************************************

// This function is called when a project is opened or re-opened (e.g. due to
// the project's config changing)

// cypress/plugins/index.js
const axios = require("axios")

module.exports = (on, config) => {
  on("task", {
    resetDb() {
      return new Promise((resolve, reject) => {
        axios({
          url: `${config.baseUrl}/graphql`,
          method: "POST",
          data: {
            query:
              'mutation { artisanCommand(command: "migrate:fresh" parameters: [{key: "--seed" value: "true"}])}',
          },
        }).then((response) => {
          const {
            data: { data, errors },
          } = response
          if (!data) {
            console.log(response)
          }
          if (errors) {
            console.log(errors)
            reject()
          }
          resolve(data.artisanCommand)
        })
      })
    },
  })

  on('task', {
    log(message) {
      console.log(message)

      return null
    },
    table(message) {
      console.table(message)

      return null
    }
  })

  on("before:browser:launch", (browser = {}, launchOptions) => {
    const REDUCE = 1
    if (browser.family === "firefox") {
      launchOptions.preferences["ui.prefersReducedMotion"] = REDUCE
    }
    if (browser.family === "chromium") {
      launchOptions.args.push("--force-prefers-reduced-motion")
    }
    return launchOptions
  })
}

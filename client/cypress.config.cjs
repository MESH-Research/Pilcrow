/* eslint-env node */
const { defineConfig } = require("cypress")

module.exports = defineConfig({
  fixturesFolder: "test/cypress/fixtures",
  screenshotsFolder: "test/cypress/screenshots",
  videosFolder: "test/cypress/videos",
  video: true,
  e2e: {
    baseUrl: "https://pilcrow.lndo.site",
    specPattern: "test/cypress/integration/**/*.cy.{js,jsx,ts,tsx}",
    supportFile: "test/cypress/support/index.js",
    setupNodeEvents(on, config) {
      const axiosImport = require("axios")
      const https = require("https")
      const axios = axiosImport.create({
        httpsAgent: new https.Agent({
          rejectUnauthorized: false
        })
      })
      on("task", {
        "before:browser:launch"(browser = {}, launchOptions) {
          const REDUCE = 1
          if (browser.family === "firefox") {
            launchOptions.preferences["ui.prefersReducedMotion"] = REDUCE
          }
          if (browser.family === "chromium" && browser.name !== "electron") {
            launchOptions.args.push("--force-prefers-reduced-motion")
          }
          return launchOptions
        },
        log(message) {
          console.log(message)
          return null
        },
        table(message) {
          console.table(message)
          return null
        },
        resetDb() {
          return new Promise((resolve, reject) => {
            axios({
              url: `${config.baseUrl}/graphql`,
              method: "POST",
              data: {
                query: "mutation { resetDatabase }"
              }
            }).then((response) => {
              const {
                data: { data, errors }
              } = response
              if (!data) {
                console.log(response)
              }
              if (errors) {
                console.log(errors)
                reject()
              }
              resolve(data.resetDatabase)
            })
          })
        }
      })
    }
  }
})

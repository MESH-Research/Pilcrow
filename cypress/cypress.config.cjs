/* global module, require, console */
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
      on("before:browser:launch", (browser = {}, launchOptions) => {
        if (browser.family === "firefox") {
          launchOptions.preferences["ui.prefersReducedMotion"] = 1
        }
        if (browser.family === "chromium" && browser.name !== "electron") {
          launchOptions.args.push("--force-prefers-reduced-motion")
        }
        return launchOptions
      })
      on("task", {
        log(message) {
          console.log(message)
          return null
        },
        table(message) {
          console.table(message)
          return null
        },
        resetDb() {
          return axios({
            url: `${config.baseUrl}/graphql`,
            method: "POST",
            data: {
              query: "mutation { resetDatabase }"
            }
          }).then((response) => {
            const {
              data: { data, errors }
            } = response
            if (errors) {
              console.log(errors)
              throw new Error(errors[0]?.message ?? "resetDatabase mutation failed")
            }
            return data.resetDatabase
          })
        }
      })
    }
  }
})

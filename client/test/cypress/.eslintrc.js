/* eslint-env node */
module.exports = {
  root: true,
  extends: [
    // Add Cypress-specific lint rules, globals and Cypress plugin
    // See https://github.com/cypress-io/eslint-plugin-cypress#rules
    "plugin:cypress/recommended",
    "prettier",
  ],
  plugins: ["prettier", "cypress"],
}

/* eslint-env node */
module.exports = {
  root: true,

  parserOptions: {
    parser: "@babel/eslint-parser",
    sourceType: "module",
  },

  env: {
    browser: true,
    "jest/globals": true,
  },

  extends: ["plugin:vue/vue3-recommended", "prettier"],

  // required to lint *.vue files
  plugins: ["vue", "prettier", "jest"],

  globals: {
    ga: true, // Google Analytics
    cordova: true,
    __statics: true,
    process: true,
    Capacitor: true,
    chrome: true,
  },

  // add your custom rules here
  rules: {
    "prefer-promise-reject-errors": "off",

    // allow debugger during development only
    "vue/script-setup-uses-vars": "error",
    "no-debugger": process.env.NODE_ENV === "production" ? "error" : "off",
    "prettier/prettier": "error",
  },
}

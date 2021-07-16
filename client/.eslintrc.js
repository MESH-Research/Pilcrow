/* eslint-env node */
module.exports = {
  root: true,

  parserOptions: {
    parser: "babel-eslint",
    sourceType: "module"
  },

  env: {
    browser: true,
    "jest/globals": true
  },

  extends: ["eslint:recommended", "prettier", "plugin:vue/recommended"],

  // required to lint *.vue files
  plugins: ["vue", "prettier", "jest"],

  globals: {
    ga: true, // Google Analytics
    cordova: true,
    __statics: true,
    process: true,
    Capacitor: true,
    chrome: true
  },

  // add your custom rules here
  rules: {
    "prefer-promise-reject-errors": "off",

    // allow debugger during development only
    "no-debugger": process.env.NODE_ENV === "production" ? "error" : "off",
    "prettier/prettier": "error"
  }
};

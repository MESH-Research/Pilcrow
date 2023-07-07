module.exports = {
    // https://eslint.org/docs/user-guide/configuring#configuration-cascading-and-hierarchy
    // This option interrupts the configuration hierarchy at this file
    // Remove this if you have an higher level ESLint config file (it usually happens into a monorepos)
    root: true,

    parserOptions: {
        ecmaVersion: "2021", // Allows for the parsing of modern ECMAScript features
        sourceType: "module",
    },

    env: {
        es6: true,
        node: true,
    },

    // Rules order is important, please avoid shuffling them
    extends: [
        "plugin:playwright/playwright-test",
        "eslint:recommended",
        "prettier",
    ],

    plugins: ["playwright", "prettier"],
    // add your custom rules here
    rules: {
        "prettier/prettier": "error",
        "prefer-promise-reject-errors": "off",
        // allow debugger during development only
        "no-debugger": process.env.NODE_ENV === "production" ? "error" : "off",
    },
    ignorePatterns: ["/playwright-report/**/*"],
}

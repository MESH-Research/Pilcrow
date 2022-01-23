/* eslint-env node */
module.exports = {
  extends: [
    // Removes 'no-undef' lint errors for Jest global functions (`describe`, `it`, etc),
    //  add Jest-specific lint rules and Jest plugin
    // See https://github.com/jest-community/eslint-plugin-jest#recommended
    "eslint:recommended",
    "prettier",
    // Uncomment following line to apply style rules
    // 'plugin:jest/style',
  ],
  plugins: ["prettier"],
  rules: {
    "prettier/prettier": "error",
  },
}

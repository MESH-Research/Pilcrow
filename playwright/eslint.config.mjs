import tseslint from "typescript-eslint";
import playwright from "eslint-plugin-playwright";

export default tseslint.config(
  {
    files: ["**/*.ts"],
    extends: [
      ...tseslint.configs.recommended,
    ],
    rules: {
      "@typescript-eslint/no-unused-vars": [
        "error",
        { argsIgnorePattern: "^_" },
      ],
    },
  },
  {
    files: ["tests/**/*.ts"],
    ...playwright.configs["flat/recommended"],
    rules: {
      ...playwright.configs["flat/recommended"].rules,
      // checkA11y and expectNotification contain assertions internally
      "playwright/expect-expect": [
        "error",
        {
          assertFunctionNames: [
            "checkA11y",
            "expectNotification",
            "expectUnreadNotification",
          ],
        },
      ],
    },
  },
);

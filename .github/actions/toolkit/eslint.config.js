import eslintConfigPrettier from 'eslint-config-prettier'
import eslint from "@eslint/js"
import tseslint from 'typescript-eslint'

export default [
    {
        files: ["src/**/*.{ts,js,mjs,cjs}"],

    },
    eslintConfigPrettier,
    eslint.configs.recommended,
    tseslint.config.recommended,

]

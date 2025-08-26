import type { CodegenConfig } from "@graphql-codegen/cli"

const config: CodegenConfig = {
  schema: "https://pilcrow.lndo.site/graphql",
  documents: [
    "./src/**/*.vue",
    "!./src/graphql/**",
    "!./src/old-pages/**",
    "./src/**/*.ts",
    "!./src/gql/**/*"
  ],
  ignoreNoDocuments: true,
  generates: {
    "./src/gql/": {
      preset: "client",
      config: {
        useTypeImports: true
      },
      presetConfig: {
        fragmentMasking: false,
        gqlTagName: "graphql"
      }
    }
  }
}

export default config

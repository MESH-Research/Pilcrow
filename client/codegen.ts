import type { CodegenConfig } from "@graphql-codegen/cli"

const config: CodegenConfig = {
  schema: process.env.GRAPHQL_SCHEMA || "http://pilcrow.lndo.site/graphql",
  documents: ["src/graphql/**/*.ts"],
  generates: {
    "src/graphql/schema.graphql": {
      plugins: ["schema-ast"],
    },
    "src/graphql/generated/graphql.ts": {
      plugins: ["typescript", "typescript-operations"],
      config: {
        namingConvention: "keep",
        maybeValue: "T | null | undefined",
      },
    },
  },
}

export default config

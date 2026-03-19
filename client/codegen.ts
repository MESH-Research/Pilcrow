import type { CodegenConfig } from "@graphql-codegen/cli"

const schema = process.env.GRAPHQL_SCHEMA || "http://pilcrow.lndo.site/graphql"

const config: CodegenConfig = {
  schema,
  documents: ["src/graphql/**/*.ts"],
  generates: {
    "src/graphql/generated/graphql.ts": {
      plugins: ["typescript", "typescript-operations"],
      config: {
        namingConvention: "keep",
        maybeValue: "T | null | undefined",
      },
    },
    "src/graphql/schema.graphql": {
      plugins: ["schema-ast"],
      config: {
        sort: true,
      },
    },
  },
}

export default config

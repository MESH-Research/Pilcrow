import type { CodegenConfig } from "@graphql-codegen/cli"

const schema = process.env.GRAPHQL_SCHEMA || "http://pilcrow.lndo.site/graphql"

const generates: CodegenConfig["generates"] = {
  "src/graphql/generated/graphql.ts": {
    plugins: ["typescript", "typescript-operations"],
    config: {
      namingConvention: "keep",
      maybeValue: "T | null | undefined",
    },
  },
}

// Only generate schema-ast when introspecting from a live server
if (!process.env.GRAPHQL_SCHEMA) {
  generates["src/graphql/schema.graphql"] = {
    plugins: ["schema-ast"],
  }
}

const config: CodegenConfig = {
  schema,
  documents: ["src/graphql/**/*.ts"],
  generates,
}

export default config

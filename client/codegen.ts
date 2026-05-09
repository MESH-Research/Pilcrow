import type { CodegenConfig } from "@graphql-codegen/cli"

const schema = process.env.GRAPHQL_SCHEMA || "http://pilcrow.lndo.site/graphql"

const config: CodegenConfig = {
  schema,
  documents: [
    "src/graphql/**/*.ts",
    "src/pages/**/*.vue",
    "src/layouts/**/*.vue",
    "src/components/**/*.vue",
    "src/routes/**/*.vue",
  ],
  generates: {
    "src/graphql/generated/": {
      preset: "client",
      presetConfig: {
        fragmentMasking: false,
      },
      config: {
        namingConvention: "keep",
        maybeValue: "T | null | undefined",
      },
    },
    "src/graphql/generated/possibleTypes.ts": {
      plugins: ["fragment-matcher"],
      config: {
        apolloClientVersion: 3,
        useExplicitTyping: true,
      },
    },
    // Always emit a normalized schema.graphql via schema-ast so the
    // committed snapshot uses graphql-js's printer regardless of source
    // (live introspection or local file). Lighthouse's printer collapses
    // single-line descriptions to "..."; we prefer the triple-quote form
    // everywhere for consistency.
    "src/graphql/schema.graphql": {
      plugins: ["schema-ast"],
      config: {
        sort: true,
      },
    },
  },
}

export default config

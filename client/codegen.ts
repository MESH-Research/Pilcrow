import type { CodegenConfig } from "@graphql-codegen/cli"

const schema = process.env.GRAPHQL_SCHEMA || "http://pilcrow.lndo.site/graphql"

const generates: CodegenConfig["generates"] = {
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
}

// Only generate schema-ast when introspecting from a live server
if (!process.env.GRAPHQL_SCHEMA) {
  generates["src/graphql/schema.graphql"] = {
    plugins: ["schema-ast"],
  }
}

const config: CodegenConfig = {
  schema,
  documents: [
    "src/graphql/**/*.ts",
    "src/pages/**/*.vue",
    "src/components/**/*.vue",
    // TODO: Remove these exclusions once the backend schema supports these queries
    "!src/pages/Admin/UserDetails.vue",
    "!src/pages/Admin/UserDetailsSubmissions.vue",
  ],
  generates,
}

export default config

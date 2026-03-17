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
  "src/graphql/generated/possibleTypes.ts": {
    plugins: ["fragment-matcher"],
    config: {
      apolloClientVersion: 3,
      useExplicitTyping: true,
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
    // TODO: Remove once commenters/CommentParticipantType are resolved
    "!src/pages/SubmissionExport.vue",
    "!src/components/atoms/ExportParticipantSelector.vue",
  ],
  generates,
}

export default config

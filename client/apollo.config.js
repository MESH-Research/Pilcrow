module.exports = {
  client: {
    service: {
      name: "ccr",
      // URL to the GraphQL API
      url: "http://ccr.lndo.site/graphql"
    },
    // Files processed by the extension
    includes: ["src/**/*.vue", "src/**/*.js"]
  }
};

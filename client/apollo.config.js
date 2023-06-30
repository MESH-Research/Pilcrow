module.exports = {
  client: {
    service: {
      name: "pilcrow",
      // URL to the GraphQL API
      url: "http://pilcrow.lndo.site/graphql"
    },
    // Files processed by the extension
    includes: ["src/**/*.vue", "src/**/*.js"],
  }
};

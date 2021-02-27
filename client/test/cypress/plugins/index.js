/* eslint-env node */
// ***********************************************************
// This example plugins/index.js can be used to load plugins
//
// You can change the location of this file or turn off loading
// the plugins file with the 'pluginsFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/plugins-guide
// ***********************************************************



// This function is called when a project is opened or re-opened (e.g. due to
// the project's config changing)

// cypress/plugins/index.js
const axios = require('axios');

module.exports = (on, config) => {
  on('task', {
    resetDb() {
      return new Promise((resolve, reject) => {
        axios({
          url: `${config.baseUrl}graphql`,
          method: "POST",
          data: {
            query: 'mutation { artisanCommand(command: "migrate:fresh" parameters: [{key: "--seed" value: ""}])}'
          }
        }).then(({ data: { data }, errors }) => {
          if (errors) {
            reject();
          }
          resolve(data.artisanCommand);
        });
      });
    }
  });
};

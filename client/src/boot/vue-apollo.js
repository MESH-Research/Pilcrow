import {
  ApolloClient,
  createHttpLink,
  InMemoryCache,
} from '@apollo/client/core'
import {
  beforeEachRequiresAuth,
  beforeEachRequiresRoles,
} from 'src/apollo/apollo-router-guards'
import { withXsrfLink, expiredTokenLink } from 'src/apollo/apollo-links.js'
import VueApollo from '@vue/apollo-option'

import { DefaultApolloClient } from '@vue/apollo-composable'
import { provide } from '@vue/composition-api'

export default function({ app, router, Vue }) {
  const apolloClient = new ApolloClient({
    link: expiredTokenLink
      .concat(withXsrfLink)
      .concat(
        createHttpLink({
          uri: process.env.GRAPHQL_URI || '/graphql',
        })
      ),
    cache: new InMemoryCache(),
  })

  /**
   * Check routes for requiresAuth meta field.
   */
  router.beforeEach(async (to, from, next) =>
    beforeEachRequiresAuth(apolloClient, to, from, next)
  )

  /**
   * Check routes for requiresRoles meta field.
   */

  router.beforeEach(async (to, from, next) =>
    beforeEachRequiresRoles(apolloClient, to, from, next)
  )


  /**
   * Setup composable apolloclient
   */
  app.mixins = (app.mixins || []).concat({
    setup() {
      provide(DefaultApolloClient, apolloClient);
    }
  });

  /**
   * Setup options api client
   */

  const apolloProvider = new VueApollo({
    defaultClient: apolloClient
  });
  Vue.use(VueApollo);
  app.apolloProvider = apolloProvider;
}

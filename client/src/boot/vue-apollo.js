import { defineBoot } from "#q-app/wrappers"
import { ApolloClient, InMemoryCache, ApolloLink } from "@apollo/client/core"
import {
  beforeEachRequiresAuth,
  beforeEachRequiresAppAdmin,
  beforeEachRequiresDraftAccess,
  beforeEachRequiresSubmissionAccess,
  beforeEachRequiresPreviewAccess,
  beforeEachRequiresViewAccess,
  beforeEachRequiresReviewAccess,
  beforeEachRequiresExportAccess,
} from "src/apollo/apollo-router-guards"
import { withXsrfLink, expiredTokenLink } from "src/apollo/apollo-links.js"
import { createApolloProvider } from "@vue/apollo-option"

import { ApolloClients } from "@vue/apollo-composable"
import createUploadLink from "apollo-upload-client/createUploadLink.mjs"
import { BatchHttpLink } from "@apollo/client/link/batch-http"

const httpOptions = {
  uri: "/graphql",
}
const httpLink = ApolloLink.split(
  (operation) => operation.getContext().hasUpload,
  createUploadLink(httpOptions),
  new BatchHttpLink(httpOptions),
)

export default defineBoot(async ({ app, router }) => {
  const apolloClient = new ApolloClient({
    link: ApolloLink.from([expiredTokenLink, withXsrfLink, httpLink]),
    cache: new InMemoryCache({
      possibleTypes: {
        Comment: [
          "InlineComment",
          "InlineCommentReply",
          "OverallComment",
          "OverallCommentReply",
        ],
      },
    }),
    connectToDevTools: true,
  })

  /**
   * Check routes for requiresAuth meta field.
   */
  router.beforeEach(async (to, from, next) =>
    beforeEachRequiresAuth(apolloClient, to, from, next),
  )

  /**
   * Check routes for requiresRoles meta field.
   */
  router.beforeEach(async (to, from, next) =>
    beforeEachRequiresAppAdmin(apolloClient, to, from, next),
  )

  /**
   * Check routes for requiresDraftAccess meta field.
   */
  router.beforeEach(async (to, from, next) =>
    beforeEachRequiresDraftAccess(apolloClient, to, from, next),
  )

  /**
   * Check routes for requiresSubmissionAccess meta field.
   */
  router.beforeEach(async (to, from, next) =>
    beforeEachRequiresSubmissionAccess(apolloClient, to, from, next),
  )

  /**
   * Check routes for requiresPreviewAccess meta field.
   */
  router.beforeEach(async (to, from, next) =>
    beforeEachRequiresPreviewAccess(apolloClient, to, from, next),
  )

  /**
   * Check routes for requiresViewAccess meta field.
   */
  router.beforeEach(async (to, from, next) =>
    beforeEachRequiresViewAccess(apolloClient, to, from, next),
  )

  /**
   * Check routes for requiresReviewAccess meta field.
   */
  router.beforeEach(async (to, from, next) =>
    beforeEachRequiresReviewAccess(apolloClient, to, from, next),
  )

  /**
   * Check routes for requiresExportAccess meta field.
   */
  router.beforeEach(async (to, from, next) =>
    beforeEachRequiresExportAccess(apolloClient, to, from, next),
  )

  const apolloClients = {
    default: apolloClient,
  }
  const apolloProvider = createApolloProvider(apolloClients)
  app.provide(ApolloClients, apolloClients) // Provide for composition api
  app.use(apolloProvider)
})

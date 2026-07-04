import { onError } from "@apollo/client/link/error"
import { Cookies } from "quasar"
import { setContext } from "@apollo/client/link/context"
import { Observable } from "@apollo/client/core"
import { readTelemetryConfig, scrub } from "src/telemetry/config"

const cookieXsrfToken = () => Cookies.get("XSRF-TOKEN")

const fetchXsrfToken = async () => {
  return fetch("/sanctum/csrf-cookie", {
    credentials: "same-origin"
  }).then(async (response) => {
    //Read response text (even though its empty) to prevent the browser from thinking there's an error b/c no one read the (empty) response body.
    await response.text()
    const xsrfToken = cookieXsrfToken()
    return xsrfToken
  })
}

const withXsrfLink = setContext((_, { headers }) => {
  //If we have a token in the header, go ahead and use it.
  if (headers && headers["X-XSRF-TOKEN"]) {
    return { headers }
  }
  //No header token, so lets look for a cookie token.
  const xsrfToken = cookieXsrfToken()
  if (xsrfToken) {
    const context = {
      headers: {
        ...headers,
        "X-XSRF-TOKEN": xsrfToken
      }
    }
    return context
  }
  //No cookie token, so we need to fetch one and set the headers that way.
  return fetchXsrfToken().then((token) => {
    return {
      headers: {
        ...headers,
        "X-XSRF-TOKEN": token
      }
    }
  })
})

//On a 419 error, fetch a new XSRF token and retry the request.
const expiredTokenLink = onError(({ operation, forward, networkError }) => {
  if (
    networkError &&
    "statusCode" in networkError &&
    networkError.statusCode == 419
  ) {
    return new Observable((observer) => {
      fetchXsrfToken()
        .then((newXsrfToken) => {
          if (!newXsrfToken) {
            throw new Error("Unable to fetch new xsrf token")
          }
          const oldHeaders = operation.getContext().headers
          operation.setContext({
            headers: {
              ...oldHeaders,
              "X-XSRF-TOKEN": newXsrfToken
            }
          })
        })
        .then(() => {
          const subscriber = {
            next: observer.next.bind(observer),
            error: observer.error.bind(observer),
            complete: observer.complete.bind(observer)
          }

          forward(operation).subscribe(subscriber)
        })
        .catch((error) => {
          observer.error(error)
        })
    })
  }
})
// Report unhandled GraphQL + network errors to Sentry when telemetry is on.
// 419 (XSRF expiry) is recovered by expiredTokenLink so we skip it here.
const telemetryErrorLink = onError(
  ({ operation, graphQLErrors, networkError }) => {
    if (!readTelemetryConfig()) return

    const opName = operation?.operationName
    const tags = { operation: opName ?? "unknown" }

    void import("@sentry/vue").then((Sentry) => {
      Sentry.withScope((scope) => {
        scope.setTag("graphql.operation", tags.operation)
        scope.setExtra("graphql.variables", scrub(operation?.variables))
        if (graphQLErrors?.length) {
          for (const err of graphQLErrors) {
            // GraphQLError arrives as POJO over the wire; wrap so Sentry
            // gets a real Error with message-based grouping instead of
            // logging "Object captured as exception".
            const wrapped =
              err instanceof Error
                ? err
                : new Error(err.message ?? "GraphQL error")
            Sentry.captureException(wrapped, {
              contexts: {
                graphql: {
                  path: err.path,
                  locations: err.locations,
                  extensions: err.extensions
                }
              }
            })
          }
        }
        if (
          networkError &&
          !("statusCode" in networkError && networkError.statusCode === 419)
        ) {
          Sentry.captureException(networkError)
        }
      })
    })
  }
)

export { withXsrfLink, expiredTokenLink, telemetryErrorLink }

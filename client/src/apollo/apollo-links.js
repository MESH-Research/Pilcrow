import { onError } from "@apollo/client/link/error";
import { Cookies } from "quasar";
import { setContext } from "@apollo/client/link/context";
import { Observable } from "@apollo/client/core";

const cookieXsrfToken = () => Cookies.get("XSRF-TOKEN");

const fetchXsrfToken = async () => {
  return fetch("/sanctum/csrf-cookie", {
    credentials: "same-origin"
  }).then(() => {
    const xsrfToken = cookieXsrfToken();
    return xsrfToken;
  });
};

const withXsrfLink = setContext((_, { headers }) => {
  //If we have a token in the header, go ahead and use it.
  if (headers && headers["X-XSRF-TOKEN"]) {
    return { headers };
  }
  //No header token, so lets look for a cookie token.
  const xsrfToken = cookieXsrfToken();
  if (xsrfToken) {
    const context = {
      headers: {
        ...headers,
        "X-XSRF-TOKEN": xsrfToken
      }
    };
    return context;
  }
  //No cookie token, so we need to fetch one and set the headers that way.
  return fetchXsrfToken().then(token => {
    return {
      headers: {
        ...headers,
        "X-XSRF-TOKEN": token
      }
    };
  });
});

//On a 419 error, fetch a new XSRF token and retry the request.
const expiredTokenLink = onError(({ operation, forward, networkError }) => {
  if (networkError && networkError.statusCode == 419) {
    return new Observable(observer => {
      fetchXsrfToken()
        .then(newXsrfToken => {
          if (!newXsrfToken) {
            throw new Error("Unable to fetch new xsrf token");
          }
          const oldHeaders = operation.getContext().headers;
          operation.setContext({
            headers: {
              ...oldHeaders,
              "X-XSRF-TOKEN": newXsrfToken
            }
          });
        })
        .then(() => {
          const subscriber = {
            next: observer.next.bind(observer),
            error: observer.error.bind(observer),
            complete: observer.complete.bind(observer)
          };

          forward(operation).subscribe(subscriber);
        })
        .catch(error => {
          observer.error(error);
        });
    });
  }
});
export { withXsrfLink, expiredTokenLink };

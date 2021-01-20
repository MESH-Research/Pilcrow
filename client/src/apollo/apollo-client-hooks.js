import { withXsrfLink, expiredTokenLink } from "./apollo-links";

export function apolloClientBeforeCreate({ apolloClientConfigObj }) {
  const httpLink = apolloClientConfigObj.link;

  const link = expiredTokenLink.concat(withXsrfLink).concat(httpLink);
  apolloClientConfigObj.link = link;
}

export function apolloClientAfterCreate(/* { apolloClient, app, router, store, ssrContext, urlPath, redirect } */) {
  // if needed you can modify here the created apollo client
}

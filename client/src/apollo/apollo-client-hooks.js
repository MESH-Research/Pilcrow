import { withXsrfLink, expiredTokenLink } from "./apollo-links";
import { SessionStorage } from "quasar";
import { CURRENT_USER } from "src/graphql/queries";
export function apolloClientBeforeCreate({ apolloClientConfigObj }) {
  const httpLink = apolloClientConfigObj.link;

  const link = expiredTokenLink.concat(withXsrfLink).concat(httpLink);
  apolloClientConfigObj.link = link;
}

export function apolloClientAfterCreate({ apolloClient, router }) {
  router.beforeEach(async (to, from, next) => {
    if (to.matched.some(record => record.meta.requiresAuth)) {
      const user = await apolloClient
        .query({
          query: CURRENT_USER
        })
        .then(({ data: { currentUser } }) => currentUser);
      if (user) {
        next();
      } else {
        SessionStorage.set("loginRedirect", to.fullPath);
        next("/login");
      }
    } else {
      next();
    }
  });
}

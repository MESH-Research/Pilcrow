import { SessionStorage } from "quasar"
import { CURRENT_USER } from "src/graphql/queries"

export async function beforeEachRequiresAuth(apolloClient, to, _, next) {
  if (to.matched.some((record) => record.meta.requiresAuth)) {
    const user = await apolloClient
      .query({
        query: CURRENT_USER,
      })
      .then(({ data: { currentUser } }) => currentUser)
    if (!user) {
      SessionStorage.set("loginRedirect", to.fullPath)
      next("/login")
    } else {
      next()
    }
  } else {
    next()
  }
}

export async function beforeEachRequiresRoles(apolloClient, to, _, next) {
  if (to.matched.some((record) => record.meta.requiresRoles)) {
    //Collect required roles
    const requiredRoles = to.matched
      .filter((record) => record.meta.requiresRoles)
      .map((record) => record.meta.requiresRoles)
      .flat(2)

    const roles = await apolloClient
      .query({
        query: CURRENT_USER,
      })
      .then(
        ({
          data: {
            currentUser: { roles },
          },
        }) => roles.map((r) => r.name)
      )
    const hasRole = requiredRoles.map((role) => roles.includes(role))
    if (!hasRole.every((role) => role === true)) {
      next({ name: "error403" })
    } else {
      next()
    }
  } else {
    next()
  }
}

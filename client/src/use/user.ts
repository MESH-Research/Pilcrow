import { useQuery, useMutation, useApolloClient } from "@vue/apollo-composable"
import { computed, reactive } from "vue"
import { CURRENT_USER } from "src/graphql/queries"
import { LOGIN, LOGOUT } from "src/graphql/mutations"
import { SessionStorage } from "quasar"
import { useVuelidate } from "@vuelidate/core"
import { required, email } from "@vuelidate/validators"
import { useRouter } from "vue-router"
import type { UserAbilities } from "src/graphql/generated/graphql"

/**
 * Admin-area visibility: the viewer may reach the /admin area if they hold ANY
 * global ability whose field is prefixed `admin_`. This is deliberately the
 * UNION of admin capabilities, not a single "can access admin" / "is super
 * admin" flag — a user granted only one admin capability still gets in, and a
 * new global role that adds an `admin_*` ability extends admin access with no
 * code change here. Holding admin access never implies super-admin authority.
 *
 * @param abilities the viewer's `currentUser.abilities` map (or null/undefined)
 */
export function hasAdminAreaAccess(
  abilities: Record<string, unknown> | null | undefined
): boolean {
  if (!abilities) {
    return false
  }
  return Object.entries(abilities).some(
    ([key, value]) => key.startsWith("admin_") && value === true
  )
}

/**
 * Returns an object of useful current user properties and helper methods:
 *
 * Query:
 *   currentUserQuery: Query Object for the current user.
 * State:
 *   currentUser<ref>: The currently logged in user.  Null if not logged in.
 *   isLoggedIn<ref>: Boolean true if a user is currently logged in.
 *   abilities<computed>: the viewer's server-resolved global ability flags.
 *   can(ability): Boolean true if the viewer holds the given global ability.
 *   canAccessAdmin<computed>: Boolean true if the viewer may access the admin area.
 *
 * @returns
 */
export function useCurrentUser() {
  const query = useQuery(CURRENT_USER)

  const currentUser = computed(() => {
    return query.result.value?.currentUser
  })

  const isLoggedIn = computed(() => {
    return !!query.result.value?.currentUser?.id
  })

  const abilities = computed(() => {
    return query.result.value?.currentUser?.abilities
  })

  /**
   * Does the viewer hold the given GLOBAL ability? Reads the server-resolved
   * `currentUser.abilities` flags (UI hints only — the server still enforces
   * every mutation). False until the query resolves and for guests (all-false).
   *
   * @param ability snake_case global ability key (a {@see UserAbilities} field)
   */
  const can = (ability: keyof UserAbilities): boolean => {
    return abilities.value?.[ability] === true
  }

  const canAccessAdmin = computed(() => hasAdminAreaAccess(abilities.value))

  return {
    currentUser,
    currentUserQuery: query,
    isLoggedIn,
    abilities,
    can,
    canAccessAdmin
  }
}

/**
 * Provides method for logging in a user
 *
 * State:
 *  v$<ref>: vuelidate validator object
 *  loading<ref>: true if the mutation is currently running
 *  redirecUrl: The url the user should be redirected to after login
 *
 * Methods:
 *  loginUser: Run login mutation using state.
 *
 * @returns Object
 */
export const useLogin = () => {
  const credentials = reactive({
    email: "",
    password: ""
  })

  const rules = {
    email: {
      email,
      required
    },
    password: {
      required
    }
  }
  const v$ = useVuelidate(rules, credentials)

  const { mutate: loginMutation, loading } = useMutation(LOGIN, () => ({
    update: (cache, { data: { login } }) => {
      cache.writeQuery({
        query: CURRENT_USER,
        data: { currentUser: { ...login } }
      })
    }
  }))

  const redirectUrl: string =
    SessionStorage.getItem<string>("loginRedirect") ?? "/dashboard"
  SessionStorage.remove("loginRedirect")

  /**
   * Login the supplied user
   *
   * @returns User object on success, throws Error otherwise.
   */
  const loginUser = async (user?: { email: string; password: string }) => {
    if (typeof user !== "undefined") {
      Object.assign(credentials, user)
    }
    v$.value.$touch()
    if (v$.value.$invalid) {
      throw Error("FORM_VALIDATION")
    }
    try {
      const result = await loginMutation({
        email: credentials.email.toLowerCase(),
        password: credentials.password
      })
      return result.data.login
    } catch (e) {
      const codes = e?.graphQLErrors
        .map((gError) => gError?.extensions?.code ?? null)
        .filter((e) => e)
      if (codes.length === 1) {
        throw Error(codes.pop())
      } else if (codes.length > 1) {
        throw Error("MULTIPLE_ERROR_CODES")
      } else {
        throw Error("INTERNAL")
      }
    }
  }

  return { loginUser, loading, v$, redirectUrl }
}

/**
 * Provides function for logging out the current user.
 *
 * Methods
 *   logoutUser: Logout the current user.
 *
 * @param {Object} router Router.  If suppled the user will be redirected on logout.
 * @returns
 */
export function useLogout() {
  const { resolveClient } = useApolloClient()
  const { push } = useRouter()
  const {
    mutate: logoutMutation,
    loading: logoutLoading,
    error: logoutError
  } = useMutation(LOGOUT, () => ({
    update: async (cache) => {
      await cache.reset()
      cache.writeQuery({ query: CURRENT_USER, data: { currentUser: null } })
    }
  }))

  /**
   * Logout the current user.
   *
   * @returns Boolean true on success, false otherwise.
   */
  async function logoutUser() {
    try {
      await logoutMutation()
      await resolveClient().resetStore()
      push("/")
      return true
    } catch (e) {
      return false
    }
  }

  return { logoutUser, logoutLoading, logoutError }
}

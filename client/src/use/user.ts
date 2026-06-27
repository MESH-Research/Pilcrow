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

  // Admin-area access is its own named gate rather than a raw `can("admin_area")`
  // at call sites: `admin_area` is not a normal capability but the server-computed
  // UNION of the viewer's admin_* abilities (see UserAbilities), so wrapping it in
  // an intention-revealing computed keeps that special meaning in one place and
  // lets it extend as new admin abilities are added. Granular admin controls still
  // gate on their specific ability via `can` (e.g. `can("admin_user_update")`).
  const canAccessAdmin = computed(() => can("admin_area"))

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

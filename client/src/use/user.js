import { useQuery, useResult, useMutation } from "@vue/apollo-composable"
import { computed, reactive } from "@vue/composition-api"
import { CURRENT_USER } from "src/graphql/queries"
import { LOGIN, LOGOUT } from "src/graphql/mutations"
import { SessionStorage } from "quasar"
import { useVuelidate } from "@vuelidate/core"
import { required, email } from "@vuelidate/validators"

/**
 * Returns an object of useful current user properties and helper methods:
 *
 * Query:
 *   currentUserQuery: Query Object for the current user.
 * State:
 *   currentUser<ref>: The currently logged in user.  Null if not logged in.
 *   isLoggedIn<ref>: Boolean true if a user is currently logged in.
 *   can<computed>: computed with signature can(ability) returns Boolean true if current user has ability
 *   hasRole<computed>: computed with signature hasRole(role) returns Boolean true if current user has role
 *
 * @returns
 */
export function useCurrentUser() {
  const query = useQuery(CURRENT_USER)

  const currentUser = useResult(query.result, null, (data) => {
    return data.currentUser
  })

  const isLoggedIn = useResult(query.result, false, (data) => {
    return !!data.currentUser.id
  })

  const abilities = useResult(
    query.result,
    [],
    (data) => data.currentUser.abilities
  )

  const roles = useResult(query.result, [], (data) => data.currentUser.roles)

  const can = computed(() => {
    return (ability) => {
      return abilities.value.includes("*") || abilities.value.includes(ability)
    }
  })

  const hasRole = computed(() => {
    return (role) => {
      if (typeof role === "undefined" || role === "*") {
        return roles.value?.length ?? 0 > 0
      }
      return roles.value.includes(role)
    }
  })

  return { currentUser, currentUserQuery: query, isLoggedIn, can, hasRole }
}

/**
 * Provides method for logging in a user
 *
 * State:
 *  $v<ref>: vuelidate validator object
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
    password: "",
  })

  const rules = {
    email: {
      email,
      required,
    },
    password: {
      required,
    },
  }
  const $v = useVuelidate(rules, credentials)

  const { mutate: loginMutation, loading } = useMutation(LOGIN, () => ({
    update: (cache, { data: { login } }) => {
      cache.writeQuery({
        query: CURRENT_USER,
        data: { currentUser: { ...login } },
      })
    },
  }))

  const redirectUrl = SessionStorage.getItem("loginRedirect") ?? "/dashboard"
  SessionStorage.remove("loginRedirect")

  /**
   * Login the supplied user
   *
   * @returns User object on success, throws Error otherwise.
   */
  const loginUser = async (user) => {
    if (typeof user !== undefined) {
      Object.assign(credentials, user)
    }
    $v.value.$touch()
    if ($v.value.$invalid) {
      throw Error("FORM_VALIDATION")
    }
    try {
      const currentUser = await loginMutation(credentials)
      return currentUser.currentUser
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

  return { loginUser, loading, $v, redirectUrl }
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
export function useLogout(router) {
  const {
    mutate: logoutMutation,
    loading: logoutLoading,
    error: logoutError,
  } = useMutation(LOGOUT, () => ({
    update: async (cache) => {
      await cache.reset()
      cache.writeQuery({ query: CURRENT_USER, data: { currentUser: null } })
    },
  }))

  /**
   * Logout the current user.
   *
   * @returns Boolean true on success, false otherwise.
   */
  async function logoutUser() {
    try {
      await logoutMutation()
      if (router) {
        router.push("/")
      }
      return true
    } catch (e) {
      return false
    }
  }

  return { logoutUser, logoutLoading, logoutError }
}

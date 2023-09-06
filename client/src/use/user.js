import { useQuery, useMutation, useApolloClient } from "@vue/apollo-composable"
import { computed, reactive } from "vue"
import { CURRENT_USER } from "src/graphql/queries"
import { LOGIN, LOGOUT } from "src/graphql/mutations"
import { SessionStorage } from "quasar"
import { useVuelidate } from "@vuelidate/core"
import { required, email } from "@vuelidate/validators"
import { useRouter } from "vue-router"

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

  const currentUser = computed(() => {
    return query.result.value?.currentUser
  })

  const isLoggedIn = computed(() => {
    return !!query.result.value?.currentUser?.id
  })

  const isAppAdmin = computed(() => {
    return !!roles.value.includes("Application Administrator")
  })

  const abilities = computed(() => {
    return query.result.value?.currentUser.abilities ?? []
  })

  const roles = computed(() => {
    return query.result.value?.currentUser.roles.map(({ name }) => name) ?? []
  })

  const isSubmitter = (submission) => {
    return submission?.submitters?.some((o) => {
      return o.id == currentUser.value.id
    })
  }
  const isReviewer = (submission) => {
    return submission?.reviewers?.some((o) => {
      return o.id == currentUser.value.id
    })
  }
  const isReviewCoordinator = (submission) => {
    return submission?.review_coordinators?.some((o) => {
      return o.id == currentUser.value.id
    })
  }
  const isEditor = (publication) => {
    return publication?.editors?.some((o) => {
      return o.id == currentUser.value.id
    })
  }
  const isPublicationAdmin = (publication) => {
    return publication?.publication_admins?.some((o) => {
      return o.id == currentUser.value.id
    })
  }

  return {
    currentUser,
    currentUserQuery: query,
    isLoggedIn,
    roles,
    abilities,
    isAppAdmin,
    isSubmitter,
    isReviewer,
    isReviewCoordinator,
    isEditor,
    isPublicationAdmin,
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
  const v$ = useVuelidate(rules, credentials)

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
        password: credentials.password,
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
      await resolveClient().resetStore()
      push("/")
      return true
    } catch (e) {
      return false
    }
  }

  return { logoutUser, logoutLoading, logoutError }
}

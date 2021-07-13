import {
    useQuery,
    useResult,
    useMutation,
  } from '@vue/apollo-composable'
import { computed } from '@vue/composition-api'
import { CURRENT_USER } from 'src/graphql/queries'
import { LOGIN, LOGOUT } from 'src/graphql/mutations'


/**
 * Returns an object of useful current user properties and helper methods:
 *
 * currentUserQuery: Query Object for the current user.
 * currentUser<ref>: The currently logged in user.  Null if not logged in.
 * isLoggedIn<ref>: Boolean true if a user is currently logged in.
 * can<computed>: computed with signature can(ability) returns Boolean true if current user has ability
 * hasRole<computed>: computed with signature hasRole(role) returns Boolean true if current user has role
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
        return abilities.value.includes('*') || abilities.value.includes(ability)
      }
    })

    const hasRole = computed(() => {
      return (role) => {
        if (typeof role === 'undefined' || role === '*') {
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
   * @returns Object
   */
  export function useLogin() {
    const { mutate: loginMutation } = useMutation(LOGIN, () => ({
      update: (cache, { data: { login } }) => {
        cache.writeQuery({
          query: CURRENT_USER,
          data: { currentUser: { ...login } },
        })
      },
    }))

    /**
     * Login the suppled user
     *
     * @param {Object} credentials
     * @returns User object on success, throws ApolloClient error otherwise.
     */
    async function loginUser(credentials) {
      const currentUser = await loginMutation(credentials)
      return currentUser.currentUser
    }

    return { loginUser }
  }

  /**
   * Provides function for logging out the current user.
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
          router.push('/')
        }
        return true
      } catch (e) {
        return false
      }
    }

    return { logoutUser, logoutLoading, logoutError }
  }

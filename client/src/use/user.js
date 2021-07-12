import {
    useQuery,
    useResult,
    useMutation,
  } from '@vue/apollo-composable'
  import { reactive, computed } from '@vue/composition-api'
  import { CURRENT_USER } from 'src/graphql/queries'
import { LOGIN, LOGOUT } from 'src/graphql/mutations'

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

  export function useLogin() {


    /**
     * Login a user.
     */
    const { mutate: loginMutation } = useMutation(LOGIN, () => ({
      update: (cache, { data: { login } }) => {
        cache.writeQuery({
          query: CURRENT_USER,
          data: { currentUser: { ...login } },
        })
      },
    }))

    async function loginUser(credentials) {
      const currentUser = await loginMutation(credentials)

      return currentUser.currentUser
    }

    return { loginUser }
  }

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

    async function logoutUser() {
      try {
        await logoutMutation()
        router.push('/')
        return true
      } catch (e) {
        return false
      }
    }

    return { logoutUser, logoutLoading, logoutError }
  }

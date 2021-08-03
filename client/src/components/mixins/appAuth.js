import gql from "graphql-tag"
import { CURRENT_USER } from "src/graphql/queries"
import { LOGIN, LOGOUT } from "src/graphql/mutations"
export default {
  methods: {
    async $login(credentials) {
      try {
        const result = await this.$apollo.mutate({
          mutation: LOGIN,
          variables: credentials,
          update: (store, { data: { login } }) => {
            store.writeQuery({
              query: CURRENT_USER,
              data: { currentUser: login },
            })
          },
        })

        return {
          success: true,
          user: result.data.login,
          result,
        }
      } catch (error) {
        if (error.graphQLErrors) {
          var errors = error.graphQLErrors
            .map((e) => e.extensions?.code ?? false)
            .filter(Boolean)
        }
        return {
          success: false,
          errors: errors ?? ["FAILURE_OTHER"],
        }
      }
    },
    async $logout() {
      try {
        const result = this.$apollo.mutate({
          mutation: LOGOUT,
          update: (store) => {
            store.writeQuery({
              query: CURRENT_USER,
              data: { currentUser: null },
            })
          },
        })
        return { success: true, result }
      } catch (error) {
        console.error(`Error while logging out: ${error}`)
        return { success: false, error }
      }
    },
  },
}

import gql from "graphql-tag";
export default {
  methods: {
    async $login(credentials) {
      try {
        const result = await this.$apollo.mutate({
          mutation: gql`
            mutation Login($email: String!, $password: String!) {
              login(email: $email, password: $password) {
                id
                name
                username
              }
            }
          `,
          variables: credentials,
          update: (store, { data: { login } }) => {
            store.writeQuery({
              query: gql`
                query currentUser {
                  me {
                    id
                    name
                    username
                  }
                }
              `,
              data: { me: login }
            });
          }
        });

        return {
          success: true,
          user: result.data.login,
          result
        };
      } catch (error) {
        if (error.graphQLErrors) {
          var errors = error.graphQLErrors
            .map(e => e.extensions?.code ?? false)
            .filter(Boolean);
        }
        return {
          success: false,
          errors: errors ?? ["FAILURE_OTHER"]
        };
      }
    },
    async $logout() {
      try {
        const result = this.$apollo.mutate({
          mutation: gql`
            mutation Logout {
              logout {
                username
                id
              }
            }
          `,
          update: store => {
            store.writeQuery({
              query: gql`
                query currentUser {
                  me
                }
              `,
              data: { me: null }
            });
          }
        });
        return { success: true, result };
      } catch (error) {
        console.error(`Error while logging out: ${error}`);
        return { success: false, error };
      }
    }
  }
};

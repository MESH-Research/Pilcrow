import { graphql } from "src/gql"

graphql(`
  fragment PaginationFields on PaginatorInfo {
    count
    currentPage
    lastPage
    perPage
  }
`)

graphql(`
  fragment RelatedUserFields on User {
    id
    display_label
    username
    name
    email
    staged
  }
`)

graphql(`
  mutation Login($email: String!, $password: String!) {
    login(email: $email, password: $password) {
      id
      display_label
      username
      name
      email
      email_verified_at
      roles {
        name
      }
      highest_privileged_role
    }
  }
`)

import gql from "graphql-tag";

export const CURRENT_USER = gql`
  query currentUser {
    currentUser {
      username
      id
      name
      email
      email_verified_at
      roles {
        name
      }
    }
  }
`;
export const GET_USERS = gql`
  query users {
    users {
      paginatorInfo {
        count
        currentPage
        lastPage
        perPage
      }
      data {
        id
        name
        email
      }
    }
  }
`;

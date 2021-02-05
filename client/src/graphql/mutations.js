import gql from "graphql-tag";

export const LOGIN = gql`
  mutation Login($email: String!, $password: String!) {
    login(email: $email, password: $password) {
      id
      name
      username
    }
  }
`;

export const LOGOUT = gql`
  mutation Logout {
    logout {
      id
    }
  }
`;

export const CREATE_USER = gql`
  mutation CreateUser(
    $email: String!
    $name: String
    $username: String!
    $password: String!
  ) {
    createUser(
      user: {
        name: $name
        email: $email
        username: $username
        password: $password
      }
    ) {
      username
      id
      created_at
    }
  }
`;

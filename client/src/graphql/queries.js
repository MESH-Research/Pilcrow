import gql from "graphql-tag";

export const CURRENT_USER = gql`
  query currentUser {
    currentUser {
      username
      id
      name
    }
  }
`;

import gql from "graphql-tag";

export const LOGIN = gql`
  mutation Login($email: String!, $password: String!) {
    login(email: $email, password: $password) {
      id
      name
      username
      email_verified_at
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

export const UPDATE_USER = gql`
  mutation UpdateUser(
    $id: ID!
    $email: String
    $name: String
    $username: String
    $password: String
  ) {
    updateUser(
      user: {
        id: $id,
        email: $email
        name: $name
        username: $username
        password: $password
      }
    ) {
      id
      email
      name
      username
      updated_at
    }
  }
`;

export const VERIFY_EMAIL = gql`
  mutation VerifyEmail($token: String!, $expires: String!) {
    verifyEmail(token: $token, expires: $expires) {
      email_verified_at
    }
  }
`;

export const SEND_VERIFY_EMAIL = gql`
  mutation SendVerificationEmail($id: ID) {
    sendEmailVerification(id: $id) {
      email
    }
  }
`;

export const CREATE_PUBLICATION = gql`
  mutation CreatePublication(
    $name: String!
  ) {
    createPublication(
      publication: {
        name: $name
      }
    ) {
      id
      name
    }
  }
`;

export const CREATE_SUBMISSION = gql`
  mutation CreateSubmission(
    $title: String!
    $publication_id: ID!
  ) {
    createSubmission(
      input: {
        title: $title
        publication_id: $publication_id
      }
    ) {
      id
      title
      publication {
        name
      }
    }
  }
`;

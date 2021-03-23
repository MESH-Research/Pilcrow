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
export const UPDATE_PROFILE_METADATA = gql`
  mutation UpdateProfileMetaData(
    $id: ID!
    $affiliation: String
    $biography: String
    $disinterest_keywords: [String!]
    $interest_keywords: [String!]
    $websites: [String!]
    $humanities_commons: String
    $orchid_id: String
    $professional_title: String
    $specialization: String
    $social_media: UpdateSocialMediaInput
  ) {
    updateUser(
      user: {
        id: $id,
        profile_metadata: {
          affiliation: $affiliation
          biography: $biography
          disinterest_keywords: $disinterest_keywords
          interest_keywords: $interest_keywords
          websites: $websites
          humanities_commons: $humanities_commons
          orchid_id: $orchid_id
          professional_title: $professional_title
          specialization: $specialization
          social_media: $social_media
        }
      }
    ) {
      id,
      profile_metadata {
        biography
        orchid_id
        humanities_commons
        professional_title
        specialization
        affiliation
        websites
        interest_keywords
        disinterest_keywords
        social_media {
          google
          twitter
          instagram
          academia_edu_id
          facebook
          linkedin
        }
      }
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

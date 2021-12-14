import gql from "graphql-tag"
import {
  PROFILE_METADATA_FRAGMENT,
  CURRENT_USER_FIELDS_FRAGMENT,
} from "./fragments"

export const LOGIN = gql`
  mutation Login($email: String!, $password: String!) {
    login(email: $email, password: $password) {
      id
      ...currentUserFields
    }
  }
  ${CURRENT_USER_FIELDS_FRAGMENT}
`

export const LOGOUT = gql`
  mutation Logout {
    logout {
      id
    }
  }
`

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
`

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
        id: $id
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
`

export const VERIFY_EMAIL = gql`
  mutation VerifyEmail($token: String!, $expires: String!) {
    verifyEmail(token: $token, expires: $expires) {
      email_verified_at
    }
  }
`

export const SEND_VERIFY_EMAIL = gql`
  mutation SendVerificationEmail($id: ID) {
    sendEmailVerification(id: $id) {
      email
    }
  }
`

export const CREATE_PUBLICATION = gql`
  mutation CreatePublication($name: String!) {
    createPublication(publication: { name: $name }) {
      id
      name
    }
  }
`

export const CREATE_PUBLICATION_USER = gql`
  mutation CreatePublicationUser(
    $user_id: ID!
    $role_id: ID!
    $publication_id: ID!
  ) {
    createPublicationUser(
      publication_user: {
        user_id: $user_id
        role_id: $role_id
        publication_id: $publication_id
      }
    ) {
      id
    }
  }
`

export const DELETE_PUBLICATION_USER = gql`
  mutation DeletePublicationUser(
    $user_id: ID!
    $role_id: ID!
    $publication_id: ID!
  ) {
    deletePublicationUser(
      publication_user: {
        user_id: $user_id
        role_id: $role_id
        publication_id: $publication_id
      }
    ) {
      id
    }
  }
`

export const CREATE_SUBMISSION = gql`
  mutation CreateSubmission(
    $title: String!
    $publication_id: ID!
    $submitter_user_id: ID!
    $file_upload: [Upload!]
  ) {
    createSubmission(
      input: {
        title: $title
        publication_id: $publication_id
        users: { connect: [{ id: $submitter_user_id, role_id: 6 }] }
        files: { create: $file_upload }
      }
    ) {
      id
      title
      publication {
        name
      }
    }
  }
`

export const CREATE_SUBMISSION_FILE = gql`
  mutation CreateSubmissionFile($submission_id: ID!, $file_upload: Upload!) {
    createSubmissionFile(
      input: { submission_id: $submission_id, file_upload: $file_upload }
    ) {
      id
      submission_id
      file_upload
    }
  }
`

export const CREATE_SUBMISSION_USER = gql`
  mutation CreateSubmissionUser(
    $user_id: ID!
    $role_id: ID!
    $submission_id: ID!
  ) {
    createSubmissionUser(
      submission_user: {
        user_id: $user_id
        role_id: $role_id
        submission_id: $submission_id
      }
    ) {
      id
    }
  }
`

export const DELETE_SUBMISSION_USER = gql`
  mutation DeleteSubmissionUser(
    $user_id: ID!
    $role_id: ID!
    $submission_id: ID!
  ) {
    deleteSubmissionUser(
      user_id: $user_id
      role_id: $role_id
      submission_id: $submission_id
    ) {
      id
    }
  }
`

export const UPDATE_PROFILE_METADATA = gql`
  mutation UpdateProfileMetaData(
    $id: ID!
    $affiliation: String
    $biography: String
    $disinterest_keywords: [String!]
    $interest_keywords: [String!]
    $websites: [String!]
    $professional_title: String
    $specialization: String
    $social_media: UpdateSocialMediaInput
    $academic_profiles: UpdateAcademicProfilesInput
  ) {
    updateUser(
      user: {
        id: $id
        profile_metadata: {
          affiliation: $affiliation
          biography: $biography
          disinterest_keywords: $disinterest_keywords
          interest_keywords: $interest_keywords
          websites: $websites
          professional_title: $professional_title
          specialization: $specialization
          social_media: $social_media
          academic_profiles: $academic_profiles
        }
      }
    ) {
      id
      ...profileMetadata
    }
  }
  ${PROFILE_METADATA_FRAGMENT}
`

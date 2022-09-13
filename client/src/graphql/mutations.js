import gql from "graphql-tag"
import {
  _COMMENT_FIELDS,
  _CURRENT_USER_FIELDS,
  _PROFILE_METADATA_FIELDS,
  _RELATED_USER_FIELDS,
} from "./fragments"

export const LOGIN = gql`
  mutation Login($email: String!, $password: String!) {
    login(email: $email, password: $password) {
      id
      ...currentUserFields
    }
  }
  ${_CURRENT_USER_FIELDS}
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

export const UPDATE_PUBLICATION_EDITORS = gql`
  mutation UpdatePublicationEditors(
    $id: ID!
    $connect: [ID!]
    $disconnect: [ID!]
  ) {
    updatePublication(
      publication: {
        id: $id
        editors: { connect: $connect, disconnect: $disconnect }
      }
    ) {
      id
      editors {
        ...relatedUserFields
      }
    }
  }
  ${_RELATED_USER_FIELDS}
`

export const UPDATE_PUBLICATION_ADMINS = gql`
  mutation UpdatePublicationAdmins(
    $id: ID!
    $connect: [ID!]
    $disconnect: [ID!]
  ) {
    updatePublication(
      publication: {
        id: $id
        publication_admins: { connect: $connect, disconnect: $disconnect }
      }
    ) {
      id
      publication_admins {
        ...relatedUserFields
      }
    }
  }
  ${_RELATED_USER_FIELDS}
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
        submitters: { connect: [$submitter_user_id] }
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

export const UPDATE_SUBMISSION_REVIEWERS = gql`
  mutation UpdateSubmissionReviewers(
    $id: ID!
    $connect: [ID!]
    $disconnect: [ID!]
  ) {
    updateSubmission(
      input: {
        id: $id
        reviewers: { connect: $connect, disconnect: $disconnect }
      }
    ) {
      id
      reviewers {
        ...relatedUserFields
      }
    }
  }
  ${_RELATED_USER_FIELDS}
`
export const UPDATE_SUBMISSION_REVIEW_COORDINATORS = gql`
  mutation UpdateSubmissionReviewCoordinators(
    $id: ID!
    $connect: [ID!]
    $disconnect: [ID!]
  ) {
    updateSubmission(
      input: {
        id: $id
        review_coordinators: { connect: $connect, disconnect: $disconnect }
      }
    ) {
      id
      review_coordinators {
        ...relatedUserFields
      }
    }
  }
  ${_RELATED_USER_FIELDS}
`

export const UPDATE_SUBMISSION_SUBMITERS = gql`
  mutation UpdateSubmissionReviewCoordinators(
    $id: ID!
    $connect: [ID!]
    $disconnect: [ID!]
  ) {
    updateSubmission(
      input: {
        id: $id
        submitters: { connect: $connect, disconnect: $disconnect }
      }
    ) {
      id
      submitters {
        ...relatedUserFields
      }
    }
  }
  ${_RELATED_USER_FIELDS}
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
  ${_PROFILE_METADATA_FIELDS}
`

export const MARK_NOTIFICATION_READ = gql`
  mutation MarkNotificationRead($notification_id: ID!) {
    markNotificationRead(id: $notification_id) {
      read_at
    }
  }
`

export const MARK_ALL_NOTIFICATIONS_READ = gql`
  mutation MarkAllNotificationsRead {
    markAllNotificationsRead
  }
`

export const UPDATE_PUBLICATION_BASICS = gql`
  mutation UpdatePublicationBasics(
    $id: ID!
    $name: String
    $is_publicly_visible: Boolean
  ) {
    updatePublication(
      publication: {
        id: $id
        name: $name
        is_publicly_visible: $is_publicly_visible
      }
    ) {
      id
      name
      is_publicly_visible
    }
  }
`

export const UPDATE_PUBLICATION_CONTENT = gql`
  mutation UpdatePublicationContent(
    $id: ID!
    $home_page_content: String
    $new_submission_content: String
  ) {
    updatePublication(
      publication: {
        id: $id
        home_page_content: $home_page_content
        new_submission_content: $new_submission_content
      }
    ) {
      id
      home_page_content
      new_submission_content
    }
  }
`

export const UPDATE_PUBLICATION_STYLE_CRITERIA = gql`
  mutation UpdatePublicationStyleCriteria(
    $publication_id: ID!
    $id: ID!
    $name: String
    $description: String
    $icon: String
  ) {
    updatePublication(
      publication: {
        id: $publication_id
        style_criterias: {
          update: [
            { id: $id, name: $name, description: $description, icon: $icon }
          ]
        }
      }
    ) {
      id
      style_criterias {
        id
        name
        description
        icon
      }
    }
  }
`

export const CREATE_PUBLICATION_STYLE_CRITERIA = gql`
  mutation CreatePublicationStyleCriteria(
    $publication_id: ID!
    $name: String!
    $description: String
    $icon: String
  ) {
    updatePublication(
      publication: {
        id: $publication_id
        style_criterias: {
          create: [{ name: $name, description: $description, icon: $icon }]
        }
      }
    ) {
      id
      style_criterias {
        id
        name
        description
        icon
      }
    }
  }
`

export const DELETE_PUBLICATION_STYLE_CRITERIA = gql`
  mutation DeletePublicationStyleCriteria($publication_id: ID!, $id: ID!) {
    updatePublication(
      publication: { id: $publication_id, style_criterias: { delete: [$id] } }
    ) {
      id
      style_criterias {
        id
        name
        description
        icon
      }
    }
  }
`

export const CREATE_OVERALL_COMMENT = gql`
  mutation CreateOverallComment($submission_id: ID!, $content: String!) {
    updateSubmission(
      input: {
        id: $submission_id
        overall_comments: { create: [{ content: $content }] }
      }
    ) {
      id
      overall_comments {
        ...commentFields
        replies {
          ...commentFields
          reply_to_id
        }
      }
    }
  }
  ${_COMMENT_FIELDS}
`

export const CREATE_OVERALL_COMMENT_REPLY = gql`
  mutation CreateOverallCommentReply(
    $submission_id: ID!
    $content: String!
    $reply_to_id: ID!
    $parent_id: ID!
  ) {
    updateSubmission(
      input: {
        id: $submission_id
        overall_comments: {
          create: [
            {
              content: $content
              reply_to_id: $reply_to_id
              parent_id: $parent_id
            }
          ]
        }
      }
    ) {
      id
      overall_comments {
        ...commentFields
        replies {
          reply_to_id
          ...commentFields
        }
      }
    }
  }
  ${_COMMENT_FIELDS}
`
export const CREATE_INLINE_COMMENT = gql`
  mutation CreateInlineCommentReply(
    $submission_id: ID!
    $content: String!
    $from: Int
    $to: Int
    $style_criteria: [ID!]
  ) {
    updateSubmission(
      input: {
        id: $submission_id
        inline_comments: {
          create: [
            {
              content: $content
              style_criteria: $style_criteria
              from: $from
              to: $to
            }
          ]
        }
      }
    ) {
      id
      inline_comments {
        style_criteria {
          name
          icon
        }
        ...commentFields
        replies {
          reply_to_id
          ...commentFields
        }
      }
    }
  }
  ${_COMMENT_FIELDS}
`

export const CREATE_INLINE_COMMENT_REPLY = gql`
  mutation CreateInlineCommentReply(
    $submission_id: ID!
    $content: String!
    $reply_to_id: ID!
    $parent_id: ID!
  ) {
    updateSubmission(
      input: {
        id: $submission_id
        inline_comments: {
          create: [
            {
              content: $content
              reply_to_id: $reply_to_id
              parent_id: $parent_id
            }
          ]
        }
      }
    ) {
      id
      inline_comments {
        style_criteria {
          name
          icon
        }
        ...commentFields
        replies {
          reply_to_id
          ...commentFields
        }
      }
    }
  }
  ${_COMMENT_FIELDS}
`

export const UPDATE_SUBMISSION_STATUS = gql`
  mutation UpdateSubmissionStatus($id: ID!, $status: SubmissionStatus) {
    updateSubmission(input: { id: $id, status: $status }) {
      id
      status
    }
  }
`

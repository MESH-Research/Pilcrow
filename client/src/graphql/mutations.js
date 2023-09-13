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
export const VERIFY_SUBMISSION_INVITE = gql`
  mutation VerifySubmissionInvite(
    $uuid: String!
    $token: String!
    $expires: String!
  ) {
    verifySubmissionInvite(uuid: $uuid, token: $token, expires: $expires) {
      id
      name
      email
      username
    }
  }
`
export const ACCEPT_SUBMISSION_INVITE = gql`
  mutation AcceptSubmissionInvite(
    $uuid: String!
    $token: String!
    $expires: String!
    $id: ID!
    $name: String
    $username: String!
    $password: String!
  ) {
    acceptSubmissionInvite(
      uuid: $uuid
      token: $token
      expires: $expires
      user: { id: $id, name: $name, username: $username, password: $password }
    ) {
      id
      name
      email
      username
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
export const CREATE_SUBMISSION_DRAFT = gql`
  mutation CreateSubmissionDraft(
    $title: String!
    $publication_id: ID!
    $submitter_user_id: ID!
  ) {
    createSubmissionDraft(
      input: {
        title: $title
        publication_id: $publication_id
        submitters: { connect: [$submitter_user_id] }
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

export const UPDATE_SUBMISSION_CONTENT = gql`
  mutation UpdateSubmissionContent($id: ID!, $content: String!) {
    updateSubmissionContent(input: { content: $content, id: $id }) {
      id
      content {
        data
      }
    }
  }
`

export const UPDATE_SUBMISSION_CONTENT_WITH_FILE = gql`
  mutation UpdateSubmissionContentWithFile($submission_id: ID!, $file_upload: Upload!) {
    updateSubmissionContentWithFile(
      input: { submission_id: $submission_id, file_upload: $file_upload }
    ) {
      id
      content {
        data
      }
    }
  }
`

export const UPDATE_SUBMISSION_TITLE = gql`
  mutation UpdateSubmissionTitle($id: ID!, $title: String!) {
    updateSubmission(input: { id: $id, title: $title }) {
      id
      title
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
    $username: String
    $name: String
    $profile_metadata: UpdateProfileMetadataInput
  ) {
    updateUser(
      user: {
        id: $id
        username: $username
        name: $name
        profile_metadata: $profile_metadata
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
    $is_accepting_submissions: Boolean
  ) {
    updatePublication(
      publication: {
        id: $id
        name: $name
        is_publicly_visible: $is_publicly_visible
        is_accepting_submissions: $is_accepting_submissions
      }
    ) {
      id
      name
      is_publicly_visible
      is_accepting_submissions
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
  mutation UpdateSubmissionStatus(
    $id: ID!
    $status: SubmissionStatus!
    $status_change_comment: String
  ) {
    updateSubmission(
      input: {
        id: $id
        status: $status
        status_change_comment: $status_change_comment
      }
    ) {
      id
      status
      status_change_comment
    }
  }
`

export const INVITE_REVIEWER = gql`
  mutation InviteReviewer($id: ID!, $email: String!, $message: String) {
    inviteReviewer(
      input: { submission_id: $id, email: $email, message: $message }
    ) {
      id
      reviewers {
        ...relatedUserFields
      }
    }
  }
  ${_RELATED_USER_FIELDS}
`

export const INVITE_REVIEW_COORDINATOR = gql`
  mutation InviteReviewCoordinator(
    $id: ID!
    $email: String!
    $message: String
  ) {
    inviteReviewCoordinator(
      input: { submission_id: $id, email: $email, message: $message }
    ) {
      id
      review_coordinators {
        ...relatedUserFields
      }
    }
  }
  ${_RELATED_USER_FIELDS}
`

export const REINVITE_REVIEWER = gql`
  mutation ReinviteReviewer($id: ID!, $email: String!, $message: String) {
    reinviteReviewer(
      input: { submission_id: $id, email: $email, message: $message }
    ) {
      id
      reviewers {
        ...relatedUserFields
      }
    }
  }
  ${_RELATED_USER_FIELDS}
`

export const REINVITE_REVIEW_COORDINATOR = gql`
  mutation ReinviteReviewCoordinator(
    $id: ID!
    $email: String!
    $message: String
  ) {
    reinviteReviewCoordinator(
      input: { submission_id: $id, email: $email, message: $message }
    ) {
      id
      review_coordinators {
        ...relatedUserFields
      }
    }
  }
  ${_RELATED_USER_FIELDS}
`

export const UPDATE_OVERALL_COMMENT = gql`
  mutation UpdateOverallComment(
    $submission_id: ID!
    $comment_id: ID!
    $content: String!
  ) {
    updateSubmission(
      input: {
        id: $submission_id
        overall_comments: { update: { id: $comment_id, content: $content } }
      }
    ) {
      id
      created_by {
        ...relatedUserFields
      }
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
  ${_RELATED_USER_FIELDS}
`

export const UPDATE_INLINE_COMMENT = gql`
  mutation UpdateInlineComment(
    $submission_id: ID!
    $comment_id: ID!
    $content: String!
    $style_criteria: [ID!]
  ) {
    updateSubmission(
      input: {
        id: $submission_id
        inline_comments: {
          update: {
            id: $comment_id
            content: $content
            style_criteria: $style_criteria
          }
        }
      }
    ) {
      id
      created_by {
        ...relatedUserFields
      }
      inline_comments {
        ...commentFields
        style_criteria {
          name
          icon
        }
        replies {
          reply_to_id
          ...commentFields
        }
      }
    }
  }
  ${_COMMENT_FIELDS}
  ${_RELATED_USER_FIELDS}
`

export const UPDATE_INLINE_COMMENT_REPLY = gql`
  mutation UpdateInlineCommentReply(
    $submission_id: ID!
    $comment_id: ID!
    $content: String!
  ) {
    updateSubmission(
      input: {
        id: $submission_id
        inline_comments: { update: { id: $comment_id, content: $content } }
      }
    ) {
      id
      created_by {
        ...relatedUserFields
      }
      inline_comments {
        ...commentFields
        replies {
          reply_to_id
          ...commentFields
        }
      }
    }
  }
  ${_COMMENT_FIELDS}
  ${_RELATED_USER_FIELDS}
`

export const UPDATE_OVERALL_COMMENT_REPLY = gql`
  mutation UpdateInlineCommentReply(
    $submission_id: ID!
    $comment_id: ID!
    $content: String!
  ) {
    updateSubmission(
      input: {
        id: $submission_id
        overall_comments: { update: { id: $comment_id, content: $content } }
      }
    ) {
      id
      created_by {
        ...relatedUserFields
      }
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
  ${_RELATED_USER_FIELDS}
`

export const REQUEST_PASSWORD_RESET = gql`
  mutation RequestPasswordReset($email: String!) {
    requestPasswordReset(email: $email)
  }
`

export const RESET_PASSWORD = gql`
  mutation ResetPassword($email: String!, $password: String!, $token: String!) {
    resetPassword(
      input: { email: $email, password: $password, token: $token }
    ) {
      id
      email
    }
  }
`

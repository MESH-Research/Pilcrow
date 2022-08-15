import gql from "graphql-tag"
import {
  _COMMENT_FIELDS,
  _CURRENT_USER_FIELDS,
  _PAGINATION_FIELDS,
  _PROFILE_METADATA_FIELDS,
  _RELATED_USER_FIELDS,
} from "./fragments"

export const CURRENT_USER = gql`
  ${_CURRENT_USER_FIELDS}
  query CurrentUser {
    currentUser {
      id
      ...currentUserFields
    }
  }
`

export const CURRENT_USER_METADATA = gql`
  ${_PROFILE_METADATA_FIELDS}
  query CurrentUserMetadata {
    currentUser {
      id
      ...profileMetadata
    }
  }
`

export const CURRENT_USER_NOTIFICATIONS = gql`
  query currentUserNotifications($page: Int, $unread: Boolean, $read: Boolean) {
    currentUser {
      id
      notifications(first: 10, page: $page, unread: $unread, read: $read) {
        paginatorInfo {
          ...paginationFields
        }
        data {
          id
          read_at
          created_at
          data {
            user {
              username
            }
            submission {
              title
            }
            publication {
              name
            }
            type
            body
          }
        }
      }
    }
  }
  ${_PAGINATION_FIELDS}
`
export const CURRENT_USER_SUBMISSIONS = gql`
  query CurrentUserSubmission {
    currentUser {
      id
      roles {
        name
      }
      submissions {
        id
        status
        my_role
        publication {
          my_role
        }
        pivot {
          id
          role_id
        }
      }
    }
  }
`

export const GET_USERS = gql`
  query GetUsers($page: Int) {
    userSearch(page: $page) {
      paginatorInfo {
        ...paginationFields
      }
      data {
        id
        name
        username
        email
      }
    }
  }
  ${_PAGINATION_FIELDS}
`

export const GET_USER = gql`
  query getUser($id: ID) {
    user(id: $id) {
      username
      email
      name
      roles {
        name
      }
    }
  }
`

export const SEARCH_USERS = gql`
  query SearchUsers($term: String, $page: Int) {
    userSearch(term: $term, page: $page) {
      paginatorInfo {
        ...paginationFields
      }
      data {
        id
        username
        name
        email
      }
    }
  }
  ${_PAGINATION_FIELDS}
`

export const GET_PUBLICATIONS = gql`
  query GetPublications($page: Int) {
    publications(page: $page) {
      paginatorInfo {
        ...paginationFields
      }
      data {
        id
        name
        home_page_content
      }
    }
  }
  ${_PAGINATION_FIELDS}
`

export const GET_SUBMISSIONS = gql`
  query GetSubmissions($page: Int) {
    submissions(page: $page) {
      paginatorInfo {
        ...paginationFields
      }
      data {
        id
        title
        status
        publication {
          name
        }
        files {
          id
          file_upload
        }
      }
    }
  }
  ${_PAGINATION_FIELDS}
`

export const GET_SUBMISSION = gql`
  query GetSubmission($id: ID!) {
    submission(id: $id) {
      id
      title
      status
      publication {
        name
        style_criterias {
          id
          name
          description
          icon
        }
      }
      reviewers {
        ...relatedUserFields
      }
      review_coordinators {
        ...relatedUserFields
      }
      submitters {
        ...relatedUserFields
      }
    }
  }
  ${_RELATED_USER_FIELDS}
`

export const GET_SUBMISSION_REVIEW = gql`
  query GetSubmissionReview($id: ID!) {
    submission(id: $id) {
      id
      title
      status
      content {
        data
      }
      publication {
        style_criterias {
          id
          name
          description
          icon
        }
      }
      inline_comments {
        from
        to
        ...commentFields
        style_criteria {
          name
          icon
        }
        replies {
          ...commentFields
          reply_to_id
        }
      }
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

export const GET_PUBLICATION = gql`
  query GetPublication($id: ID!) {
    publication(id: $id) {
      id
      name
      is_publicly_visible
      effective_role
      home_page_content
      new_submission_content
      style_criterias {
        name
        id
        icon
        description
      }
      publication_admins {
        ...relatedUserFields
      }
      editors {
        ...relatedUserFields
      }
    }
  }
  ${_RELATED_USER_FIELDS}
`

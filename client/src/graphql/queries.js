import gql from "graphql-tag"
import {
  _CURRENT_USER_FIELDS,
  _PAGINATION_FIELDS,
  _PROFILE_METADATA_FIELDS,
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
      submissions {
        id
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
      title
      publication {
        name
        style_criterias {
          id
          name
          description
          icon
        }
      }
      users {
        name
        username
        email
        pivot {
          id
          user_id
          role_id
        }
      }
    }
  }
`

export const GET_PUBLICATION = gql`
  query GetPublication($id: ID!) {
    publication(id: $id) {
      name
      is_publicly_visible
      users {
        name
        email
        username
        pivot {
          id
          user_id
          role_id
        }
      }
    }
  }
`

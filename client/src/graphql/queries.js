import gql from "graphql-tag"
import {
  PROFILE_METADATA_FRAGMENT,
  CURRENT_USER_FIELDS_FRAGMENT,
} from "./fragments"

export const CURRENT_USER = gql`
  ${CURRENT_USER_FIELDS_FRAGMENT}
  query CurrentUser {
    currentUser {
      id
      ...currentUserFields
    }
  }
`

export const CURRENT_USER_METADATA = gql`
  ${PROFILE_METADATA_FRAGMENT}
  query CurrentUserMetadata {
    currentUser {
      id
      ...profileMetadata
    }
  }
`
export const CURRENT_USER_NOTIFICATIONS = gql`
  query currentUserNotifications($page: Int) {
    currentUser {
      id
      notifications(first: 10, page: $page) {
        paginatorInfo {
          count
          currentPage
          lastPage
          perPage
        }
        data {
          id
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
            read_at
          }
        }
      }
    }
  }
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
  query users($page: Int) {
    userSearch(page: $page) {
      paginatorInfo {
        count
        currentPage
        lastPage
        perPage
      }
      data {
        id
        name
        username
        email
      }
    }
  }
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
  query users($term: String, $page: Int) {
    userSearch(term: $term, page: $page) {
      paginatorInfo {
        count
        currentPage
        lastPage
        perPage
      }
      data {
        id
        username
        name
        email
      }
    }
  }
`

export const GET_PUBLICATIONS = gql`
  query GetPublications($page: Int) {
    publications(page: $page) {
      paginatorInfo {
        count
        currentPage
        lastPage
        perPage
      }
      data {
        id
        name
      }
    }
  }
`

export const GET_SUBMISSIONS = gql`
  query GetSubmissions($page: Int) {
    submissions(page: $page) {
      paginatorInfo {
        count
        currentPage
        lastPage
        perPage
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
`

export const GET_SUBMISSION = gql`
  query GetSubmission($id: ID!) {
    submission(id: $id) {
      title
      publication {
        name
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

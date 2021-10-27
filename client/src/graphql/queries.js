import gql from "graphql-tag"

export const CURRENT_USER = gql`
  query currentUser {
    currentUser {
      username
      id
      name
      email
      email_verified_at
      roles {
        name
      }
    }
  }
`
export const PROFILE_METADATA_FRAGMENT = gql`
  fragment profileMetadata on User {
    profile_metadata {
      biography
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
        facebook
        linkedin
      }
      academic_profiles {
        humanities_commons
        orcid_id
        academia_edu_id
      }
    }
  }
`

export const CURRENT_USER_METADATA = gql`
  query currentUser {
    currentUser {
      id
      ...profileMetadata
    }
  }
  ${PROFILE_METADATA_FRAGMENT}
`
export const CURRENT_USER_SUBMISSIONS = gql`
  query currentUser {
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
  query GetSubmission($id: ID) {
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

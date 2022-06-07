import gql from "graphql-tag"

export const _PROFILE_METADATA_FIELDS = gql`
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

export const _CURRENT_USER_FIELDS = gql`
  fragment currentUserFields on User {
    username
    name
    email
    email_verified_at
    roles {
      name
    }
  }
`

export const _RELATED_USER_FIELDS = gql`
  fragment relatedUserFields on User {
    id
    username
    name
    email
  }
`

export const _PAGINATION_FIELDS = gql`
  fragment paginationFields on PaginatorInfo {
    count
    currentPage
    lastPage
    perPage
  }
`

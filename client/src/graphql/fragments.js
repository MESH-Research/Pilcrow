import gql from "graphql-tag"

export const _PROFILE_METADATA_FIELDS = gql`
  fragment profileMetadata on User {
    profile_metadata {
      biography
      position_title
      specialization
      affiliation
      websites
      interest_keywords
      social_media {
        twitter
        instagram
        facebook
        linkedin
      }
      academic_profiles {
        humanities_commons
        orcid_id
      }
    }
  }
`

export const _CURRENT_USER_FIELDS = gql`
  fragment currentUserFields on User {
    display_label
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
    display_label
    username
    name
    email
    staged
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

export const _COMMENT_FIELDS = gql`
  fragment commentFields on Comment {
    id
    content
    created_at
    updated_at
    updated_by {
      ...relatedUserFields
    }
    created_by {
      ...relatedUserFields
    }
  }
  ${_RELATED_USER_FIELDS}
`

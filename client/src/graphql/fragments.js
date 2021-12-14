import gql from "graphql-tag"

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

export const CURRENT_USER_FIELDS_FRAGMENT = gql`
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

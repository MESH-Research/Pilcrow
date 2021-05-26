import gql from "graphql-tag";

export const CURRENT_USER = gql`
  query currentUser {
    currentUser {
      username
      id
      name
      email
      email_verified_at
      profile_metadata {
        academic_profiles {
          orcid_id
          humanities_commons
          academia_edu_id
        }
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
      }
      roles {
        name
      }
    }
  }
`;
export const GET_USERS = gql`
  query users($page:Int) {
    userSearch(page:$page) {
      paginatorInfo {
        count
        currentPage
        lastPage
        perPage
      }
      data {
        id
        name
        email
      }
    }
  }
`;
export const GET_PUBLICATIONS = gql`
  query GetPublications($page:Int) {
    publications(page:$page) {
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
`;

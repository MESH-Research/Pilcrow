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
        biography
        orchid_id
        humanities_commons
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
          academia_edu_id
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
    users(page:$page) {
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

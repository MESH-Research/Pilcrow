import { graphql } from "src/gql"

graphql(`
  fragment PaginationFields on PaginatorInfo {
    count
    currentPage
    lastPage
    perPage
  }
`)

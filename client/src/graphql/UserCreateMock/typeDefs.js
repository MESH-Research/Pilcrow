import gql from 'graphql-tag'

export const typeDefs = gql`

    scalar DateTime

    type User {
        id: String!
        name: String!
        email: String!
        email_verified_at: String!
        created_at: DateTime!
        updated_at: DateTime!
    }

    
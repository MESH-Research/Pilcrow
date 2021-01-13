import gql from 'graphql-tag'
import { v4 } from 'uuid';

export const typeDefs = gql`

    scalar DateTime

    type Mutation {
        userCreate(name: String!, email: String!, password: String!): User!
    }

    type Query {
        userEmailAvailable(email: String!): Boolean!
        user
    }

    type User {
        id: ID!
        name: String!
        email: String!
        email_verified_at: DateTime
        created_at: DateTime!
        updated_at: DateTime
    }
    `;
const registeredEmails = ['wreality@gmail.com'];
export const resolvers = {
    Mutation: {
        userCreate: (_, { name, email }) => {
            return {
                id: v4(),
                created_at: 'date',
                name, email
            }
        }
    },
    Query: {
        userEmailAvailable: (_, { email }) => {
            return !registeredEmails.includes(email);
        },
        user: () => {
            return { name: "Brian", email: "test@example.com", created_at: "20212-2020" }
        }
    }
};

# GraphQL

Pilcrow uses [GraphQL](https://graphql.org/) for communication between the client and backend. The backend schema is defined using [Lighthouse](https://lighthouse-php.com/), and the client consumes it via [Apollo Client](https://www.apollographql.com/docs/react/).

- [Backend GraphQL](./graphql-backend) — Schema definition, Lighthouse directives, and exporting the compiled schema
- [Client GraphQL](./graphql-client) — Operations, TypeScript code generation, and development workflow

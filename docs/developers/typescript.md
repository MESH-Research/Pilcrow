# TypeScript

All client code is TypeScript. New code must use TypeScript, and types should
flow from the GraphQL schema through codegen into components and tests.

For GraphQL code generation setup and configuration, see
[GraphQL Client](./graphql-client).

## Type Flow

```mermaid
flowchart LR
    subgraph gql [" "]
        gqlIcon@{ icon: "logos:graphql", label: " ", pos: "b", h: 32 }
        A@{ shape: doc, label: "Schema Source<br/><i>(introspection or compiled)</i>" }
    end
    B(["Codegen"])
    subgraph ts [" "]
        tsIcon@{ icon: "logos:typescript-icon", label: " ", pos: "b", h: 32 }
        C@{ shape: doc, label: "Generated Types" }
    end
    E@{ shape: processes, label: "Composables" }
    D@{ shape: processes, label: "Components" }
    F@{ shape: processes, label: "Tests" }
    A --> B --> C
    C --> E
    C --> D
    C --> F
    style gql fill:none,stroke:#ddd,stroke-dasharray: 5 5
    style ts fill:none,stroke:#ddd,stroke-dasharray: 5 5
```

When a field is renamed or removed in the backend schema, the generated types
change, and TypeScript surfaces errors in every component and test that
references the old shape. This is the primary value of typing mock data in tests
-- schema changes become compile errors instead of silent failures.

- [Conventions](./typescript-conventions) -- component patterns, provide/inject,
  utility types
- [GraphQL Types](./typescript-graphql) -- using generated types in tests

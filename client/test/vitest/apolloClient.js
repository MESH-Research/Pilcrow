import { createMockClient as createClient } from "mock-apollo-client"

export const createMockClient = (opts) => createClient({...opts, connectToDevTools: false})


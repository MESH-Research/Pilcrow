import { createMockClient as createClient } from "mock-apollo-client"
import { config } from "@vue/test-utils"
import { cloneDeep } from "lodash"
import { beforeAll, afterAll, vi } from "vitest"
import { ApolloClients } from "@vue/apollo-composable"

const createMockClient = (opts) => createClient({ ...opts, connectToDevTools: false })
export { createMockClient }

export function installApolloClient(opts) {
  const globalConfigBackup = cloneDeep(config.global)
  const client = createMockClient(opts)

  const docs = new Map()

  beforeAll(() => {
    config.global.provide = {
      ...config.global.provide,
      [ApolloClients]: { default: client },
    }
  })

  afterAll(() => {
    config.global = globalConfigBackup
  })

  const mockMethods = {
    getRequestHandler(query) {
      if (!docs.has(query)) {
        docs.set(query, vi.fn())
        client.setRequestHandler(query, docs.get(query))
      }
      return docs.get(query)
    },
    mockReset() {
      docs.forEach((handler) => handler.mockReset())
    }
  }

   return Object.assign(client, mockMethods)
}
import MetadataPage from "./MetadataPage.vue"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { ApolloClients } from "@vue/apollo-composable"
import { createMockClient } from "mock-apollo-client"

jest.mock("vue-i18n", () => ({
  useI18n: () => ({
    te: () => true,
  }),
}))

jest.mock("src/use/forms")
installQuasarPlugin()
describe("MetadataPage", () => {
  const mockClient = createMockClient()
  const makeWrapper = () => {
    return mount(MetadataPage, {
      global: {
        provide: {
          [ApolloClients]: { default: mockClient },
        },
        mocks: {
          $t: (t) => t,
        },
      },
    })
  }

  beforeEach(() => {
    jest.resetAllMocks()
  })

  test("able to mount", () => {
    const wrapper = makeWrapper()
    expect(wrapper).toBeTruthy()
  })
})

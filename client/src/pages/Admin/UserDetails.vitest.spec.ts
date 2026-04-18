import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import UserDetails from "./UserDetails.vue"
import {
  getUserPublicationsDocument,
  type getUserPublicationsQuery,
  PublicationRole
} from "src/graphql/generated/graphql"
import { describe, expect, it, vi } from "vitest"

vi.mock("vue-router", () => ({
  useRouter: () => ({
    push: vi.fn(),
    replace: vi.fn()
  }),
  useRoute: () => ({
    query: {}
  })
}))

installQuasarPlugin()
const mockClient = installApolloClient()

describe("UserDetails publications tab", () => {
  const mockPublicationsResponse: { data: getUserPublicationsQuery } = {
    data: {
      user: {
        id: "1",
        publications: {
          paginatorInfo: {
            __typename: "PaginatorInfo",
            count: 2,
            currentPage: 1,
            lastPage: 1,
            perPage: 25,
            total: 2
          },
          data: [
            {
              id: "1",
              role: PublicationRole.editor,
              publication: { id: "10", name: "Test Publication" }
            },
            {
              id: "2",
              role: PublicationRole.publication_admin,
              publication: { id: "20", name: "Another Publication" }
            }
          ]
        }
      }
    }
  }

  const wrapperFactory = () =>
    mount(UserDetails, {
      global: { stubs: ["router-link"] },
      props: { id: "1" }
    })

  it("mounts without errors", () => {
    expect(wrapperFactory()).toBeTruthy()
  })

  it("displays publications in the table", async () => {
    mockClient
      .getRequestHandler(getUserPublicationsDocument)
      .mockResolvedValue(mockPublicationsResponse)

    const wrapper = wrapperFactory()
    await flushPromises()

    const rows = wrapper.findAll("tbody tr")
    expect(rows).toHaveLength(2)
  })
})

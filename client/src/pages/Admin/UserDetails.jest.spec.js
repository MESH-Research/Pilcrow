import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { ApolloClients } from "@vue/apollo-composable"
import { mount } from "@vue/test-utils"
import flushPromises from "flush-promises"
import { createMockClient } from "mock-apollo-client"
import { GET_USER } from "src/graphql/queries"
import UserDetails from "./UserDetails.vue"

installQuasarPlugin()
describe("User Details page mount", () => {
  const mockClient = createMockClient()
  const wrapperFactory = async (id) => {
    const wrapper = mount(UserDetails, {
      global: {
        provide: {
          [ApolloClients]: { default: mockClient },
        },
        mocks: {
          $t: (t) => t,
          $tc: (t) => t,
        },
        stubs: ["router-link"],
      },
      props: {
        id,
      },
    })
    await flushPromises()
    return wrapper
  }

  const getUserHandler = jest.fn()
  mockClient.setRequestHandler(GET_USER, getUserHandler)

  beforeEach(() => {
    jest.resetAllMocks()
  })

  it("mounts without errors", async () => {
    expect(await wrapperFactory("0")).toBeTruthy()
    expect(getUserHandler).toBeCalledWith({ id: "0" })
  })

  it("queries for a specific user", async () => {
    const wrapper = await wrapperFactory("1")
    expect(wrapper).toBeTruthy()
    expect(getUserHandler).toBeCalledWith({ id: "1" })
  })

  it("reflects the lack of roles for a user with no assigned roles", async () => {
    getUserHandler.mockResolvedValue({
      data: {
        user: {
          username: "Username",
          email: "email",
          name: "Regular User",
          roles: [],
        },
      },
    })

    const wrapper = await wrapperFactory("1")
    expect(wrapper.text()).toContain("role.no_roles_assigned")
  })

  it("reflects the role of an application administrator", async () => {
    getUserHandler.mockResolvedValue({
      data: {
        user: {
          name: "Application Admin User",
          username: "Username",
          email: "email",
          roles: [
            {
              name: "Application Administrator",
            },
          ],
        },
      },
    })

    const wrapper = await wrapperFactory("2")
    expect(wrapper).toBeTruthy()
    //TODO: Make this test less fragile by finding the roles list component/el first
    expect(wrapper.text()).toContain("Application Administrator")
  })

  it("reflects the lack of display name for a user with no name", async () => {
    getUserHandler.mockResolvedValue({
      data: {
        user: {
          name: null,
          email: "email",
          username: "userWithNoName",
          roles: [],
        },
      },
    })
    const wrapper = await wrapperFactory("3")
    expect(wrapper).toBeTruthy()
    expect(wrapper.text()).toContain("user.empty_name")
  })
})

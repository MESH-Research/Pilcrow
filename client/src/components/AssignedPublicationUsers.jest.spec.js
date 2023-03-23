import AssignedPublicationUsers from "./AssignedPublicationUsers.vue"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { createMockClient } from "mock-apollo-client"
import { ApolloClients } from "@vue/apollo-composable"
import {
  UPDATE_PUBLICATION_EDITORS,
  UPDATE_PUBLICATION_ADMINS,
} from "src/graphql/mutations"

jest.mock("quasar", () => ({
  ...jest.requireActual("quasar"),
  useQuasar: () => ({
    notify: jest.fn(),
  }),
}))

installQuasarPlugin()
describe("AssignedPublicationUsers", () => {
  const mockClient = createMockClient({
    defaultOptions: { watchQuery: { fetchPolicy: "network-only" } },
  })
  const makeWrapper = (props) => {
    return mount(AssignedPublicationUsers, {
      global: {
        mocks: {
          $t: (t) => t,
        },
        provide: {
          [ApolloClients]: { default: mockClient },
        },
      },
      props,
    })
  }
  const editorsMutation = jest.fn()
  const publicationAdministratorsMutation = jest.fn()

  mockClient.setRequestHandler(UPDATE_PUBLICATION_EDITORS, editorsMutation)
  mockClient.setRequestHandler(
    UPDATE_PUBLICATION_ADMINS,
    publicationAdministratorsMutation
  )

  beforeEach(() => {
    jest.resetAllMocks()
  })

  test("shows role users", () => {
    const wrapper = makeWrapper({
      role: "editors",
      container: {
        __typename: "Publication",
        id: 1,
        editors: [
          { id: 1, email: "test@example.com", name: "TestUser" },
          { id: 2, email: "test2@example.com", name: "TestUser2" },
        ],
      },
    })
    expect(wrapper).toBeTruthy()
    expect(wrapper.findAll(".q-item")).toHaveLength(2)
  })

  test("shows empty card if no users", async () => {
    const wrapper = makeWrapper({
      role: "editors",
      container: {
        __typename: "Publication",
        id: 1,
        editors: [],
      },
    })
    expect(wrapper.findComponent({ ref: "card_no_users" }).exists()).toBe(true)

    await wrapper.setProps({
      role: "editors",
      container: {
        __typename: "Publication",
        id: 1,
        editors: [{ id: 1, email: "test@example.com", name: "TestUser" }],
      },
    })
    expect(wrapper.findComponent({ ref: "card_no_users" }).exists()).toBe(false)
    expect(wrapper.findAll(".q-item")).toHaveLength(1)
  })

  test("mutable prop hides mutation controls", async () => {
    const wrapper = makeWrapper({
      role: "editors",
      container: {
        __typename: "Publication",
        id: 1,
        editors: [
          {
            __typename: "User",
            id: 1,
            email: "test@example.com",
            name: "Test",
          },
        ],
      },
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(false)
    expect(wrapper.find("[data-cy=button_unassign]").exists()).toBe(false)

    await wrapper.setProps({
      role: "editors",
      mutable: true,
      container: {
        __typename: "Publication",
        id: 1,
        editors: [{ id: 1, email: "test@example.com", name: "Test" }],
      },
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(true)
    expect(wrapper.find("[data-cy=button_unassign]").exists()).toBe(true)
  })

  test("maxUsers prop hides assign controls", async () => {
    const wrapper = makeWrapper({
      role: "editors",
      mutable: true,
      maxUsers: 1,
      container: {
        __typename: "Publication",
        id: 1,
        editors: [{ id: 1, email: "test@example.com", name: "Test" }],
      },
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(false)

    await wrapper.setProps({
      role: "editors",
      mutable: true,
      maxUsers: 2,
      container: {
        __typename: "Publication",
        id: 1,
        editors: [{ id: 1, email: "test@example.com", name: "Test" }],
      },
    })
    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(true)
  })

  test("mutable props disables mutations", async () => {
    const wrapper = makeWrapper({
      role: "editors",
      container: {
        __typename: "Publication",
        id: 1,
        editors: [{ id: 1, email: "test@example.com", name: "Test" }],
      },
    })

    wrapper.vm.user = { user: { id: 1 } }
    await wrapper.vm.handleUserListClick({ user: { id: 1 } })
    await wrapper.vm.handleSubmit()

    expect(editorsMutation).not.toHaveBeenCalled()
  })

  test("maxUsers prop disables assignment mutations, unassign still allowed", async () => {
    const wrapper = makeWrapper({
      role: "editors",
      mutable: true,
      maxUsers: 1,
      container: {
        __typename: "Publications",
        id: 1,
        editors: [{ id: 1, email: "test@example.com", name: "Test" }],
      },
    })

    wrapper.vm.user = { user: { id: 1 } }
    await wrapper.vm.handleSubmit()
    expect(editorsMutation).not.toHaveBeenCalled()
    await wrapper.vm.handleUserListClick({ user: { id: 1 } })
    expect(editorsMutation).toHaveBeenCalledWith({
      disconnect: [1],
      id: 1,
    })
  })

  test("assignment mutation called with correct variables", async () => {
    const wrapper = makeWrapper({
      role: "editors",
      mutable: true,
      container: {
        __typename: "Publication",
        id: 1,
        editors: [{ id: 1, email: "test@example.com", name: "Test" }],
      },
    })
    wrapper.vm.user = { id: 1 }
    await wrapper.vm.handleSubmit()
    expect(editorsMutation).toHaveBeenCalledWith({
      connect: [1],
      id: 1,
    })
  })

  test.each([
    ["editors", editorsMutation],
    ["publication_admins", publicationAdministratorsMutation],
  ])("Calls %s mutation", async (role, mock) => {
    const props = {
      role,
      mutable: true,
      container: {
        id: 1,
        __typename: "Publication",
      },
    }
    props.container[role] = [
      { id: 1, username: "Test", email: "test@example.com" },
    ]
    const wrapper = makeWrapper(props)

    wrapper.vm.user = { id: 2 }
    await wrapper.vm.handleSubmit()
    expect(mock).toHaveBeenCalledWith({ connect: [2], id: 1 })

    mock.mockClear()
    await wrapper.vm.handleUserListClick({ user: { id: 2 } })
    expect(mock).toHaveBeenCalledWith({ disconnect: [2], id: 1 })
  })
})

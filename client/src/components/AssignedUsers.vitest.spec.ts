import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { Notify } from "quasar"

import { UpdatePublicationUsersDocument } from "src/gql/graphql"
import AssignedUsers from "./AssignedUsers.vue"

import { beforeEach, describe, expect, test, vi } from "vitest"

installQuasarPlugin({ plugins: { Notify } })
const mockClient = installApolloClient()

describe("AssignedPublicationUsers", () => {
  const makeWrapper = (props) => {
    return mount(AssignedUsers, {
      props
    })
  }
  const mutation = vi.fn()

  mockClient.setRequestHandler(UpdatePublicationUsersDocument, mutation)

  beforeEach(() => {
    vi.resetAllMocks()
  })

  test("shows role users", () => {
    const wrapper = makeWrapper({
      users: [
        { id: 1, email: "test@example.com", name: "TestUser" },
        { id: 2, email: "test2@example.com", name: "TestUser2" }
      ]
    })
    expect(wrapper).toBeTruthy()
    expect(wrapper.findAll(".q-item")).toHaveLength(2)
  })

  test("shows empty card if no users", async () => {
    const wrapper = makeWrapper({
      users: []
    })
    expect(wrapper.findComponent({ ref: "card_no_users" }).exists()).toBe(true)

    await wrapper.setProps({
      users: [
        {
          id: "1",
          email: "test@example.com",
          name: "TestUser",
          username: "testuser"
        }
      ]
    })
    expect(wrapper.findComponent({ ref: "card_no_users" }).exists()).toBe(false)
    expect(wrapper.findAll(".q-item")).toHaveLength(1)
  })

  test("mutable prop hides mutation controls", async () => {
    const wrapper = makeWrapper({
      users: [
        {
          __typename: "User",
          id: 1,
          email: "test@example.com",
          name: "Test"
        }
      ],
      mutable: false
    })
    expect(wrapper.findComponent("q-form").exists()).toBe(false)
    expect(wrapper.find("[data-cy=button_unassign]").exists()).toBe(false)

    await wrapper.setProps({
      mutable: true,
      users: [
        { id: "1", email: "test@example.com", name: "Test", username: "test" }
      ]
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(true)
    expect(wrapper.find("[data-cy=button_unassign]").exists()).toBe(true)
  })

  test("maxUsers prop hides assign controls", async () => {
    const wrapper = makeWrapper({
      mutable: true,
      maxUsers: 1,
      users: [{ id: 1, email: "test@example.com", name: "Test" }]
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(false)

    await wrapper.setProps({
      mutable: true,
      maxUsers: 2,
      users: [
        { id: "1", email: "test@example.com", name: "Test", username: "test" }
      ]
    })
    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(true)
  })

  test("assignment mutation called with correct variables", async () => {
    const wrapper = makeWrapper({
      mutable: true,
      users: [{ id: 1, email: "test@example.com", name: "Test" }]
    })
    wrapper.vm.user = { id: 1 }
    await wrapper.vm.handleSubmit()
    expect(mutation).toHaveBeenCalledWith({
      connect: [1],
      id: 1
    })
  })

  test.each([
    ["editors", mutation],
    ["publication_admins", mutation]
  ])("Calls %s mutation", async (roleGroup, mock) => {
    const props = {
      roleGroup,
      mutable: true,
      container: {
        id: 1,
        __typename: "Publication"
      }
    }
    props.container[roleGroup] = [
      { id: 1, username: "Test", email: "test@example.com" }
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

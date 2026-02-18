import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { Notify } from "quasar"
import {
  UPDATE_PUBLICATION_ADMINS,
  UPDATE_PUBLICATION_EDITORS
} from "src/graphql/mutations"
import type { Publication, User } from "src/graphql/generated/graphql"
import AssignedPublicationUsers from "./AssignedPublicationUsers.vue"

import { beforeEach, describe, expect, test, vi } from "vitest"

installQuasarPlugin({ plugins: { Notify } })
const mockClient = installApolloClient()

describe("AssignedPublicationUsers", () => {
  const makeWrapper = (props: {
    container: Publication
    roleGroup: string
    mutable?: boolean
    maxUsers?: number | boolean
  }) => {
    return mount(AssignedPublicationUsers, {
      props
    })
  }
  const editorsMutation = vi.fn()
  const publicationAdministratorsMutation = vi.fn()

  mockClient.setRequestHandler(UPDATE_PUBLICATION_EDITORS, editorsMutation)
  mockClient.setRequestHandler(
    UPDATE_PUBLICATION_ADMINS,
    publicationAdministratorsMutation
  )

  beforeEach(() => {
    vi.resetAllMocks()
  })

  test("shows role users", () => {
    const wrapper = makeWrapper({
      roleGroup: "editors",
      container: {
        __typename: "Publication",
        id: "1",
        editors: [
          { id: "1", email: "test@example.com", name: "TestUser" } as User,
          { id: "2", email: "test2@example.com", name: "TestUser2" } as User
        ]
      } as Publication
    })
    expect(wrapper).toBeTruthy()
    expect(wrapper.findAll(".q-item")).toHaveLength(2)
  })

  test("shows empty card if no users", async () => {
    const wrapper = makeWrapper({
      roleGroup: "editors",
      container: {
        __typename: "Publication",
        id: "1",
        editors: []
      } as Publication
    })
    expect(wrapper.findComponent({ ref: "card_no_users" }).exists()).toBe(true)

    await wrapper.setProps({
      roleGroup: "editors",
      container: {
        __typename: "Publication",
        id: "1",
        editors: [
          { id: "1", email: "test@example.com", name: "TestUser" } as User
        ]
      } as Publication
    })
    expect(wrapper.findComponent({ ref: "card_no_users" }).exists()).toBe(false)
    expect(wrapper.findAll(".q-item")).toHaveLength(1)
  })

  test("mutable prop hides mutation controls", async () => {
    const wrapper = makeWrapper({
      roleGroup: "editors",
      container: {
        __typename: "Publication",
        id: "1",
        editors: [
          {
            __typename: "User",
            id: "1",
            email: "test@example.com",
            name: "Test"
          } as User
        ]
      } as Publication
    })
    expect(wrapper.findComponent("q-form").exists()).toBe(false)
    expect(wrapper.find("[data-cy=button_unassign]").exists()).toBe(false)

    await wrapper.setProps({
      roleGroup: "editors",
      mutable: true,
      container: {
        __typename: "Publication",
        id: "1",
        editors: [
          { id: "1", email: "test@example.com", name: "Test" } as User
        ]
      } as Publication
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(true)
    expect(wrapper.find("[data-cy=button_unassign]").exists()).toBe(true)
  })

  test("maxUsers prop hides assign controls", async () => {
    const wrapper = makeWrapper({
      roleGroup: "editors",
      mutable: true,
      maxUsers: 1,
      container: {
        __typename: "Publication",
        id: "1",
        editors: [
          { id: "1", email: "test@example.com", name: "Test" } as User
        ]
      } as Publication
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(false)

    await wrapper.setProps({
      roleGroup: "editors",
      mutable: true,
      maxUsers: 2,
      container: {
        __typename: "Publication",
        id: "1",
        editors: [
          { id: "1", email: "test@example.com", name: "Test" } as User
        ]
      } as Publication
    })
    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(true)
  })

  test("mutable props disables mutations", async () => {
    const wrapper = makeWrapper({
      roleGroup: "editors",
      container: {
        __typename: "Publication",
        id: "1",
        editors: [
          { id: "1", email: "test@example.com", name: "Test" } as User
        ]
      } as Publication
    })

    ;(wrapper.vm as any).user = { user: { id: "1" } }
    await (wrapper.vm as any).handleUserListClick({ user: { id: "1" } })
    await (wrapper.vm as any).handleSubmit()

    expect(editorsMutation).not.toHaveBeenCalled()
  })

  test("maxUsers prop disables assignment mutations, unassign still allowed", async () => {
    const wrapper = makeWrapper({
      roleGroup: "editors",
      mutable: true,
      maxUsers: 1,
      container: {
        __typename: "Publication",
        id: "1",
        editors: [
          { id: "1", email: "test@example.com", name: "Test" } as User
        ]
      } as Publication
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(false)
    expect(wrapper.find("[data-cy=button_unassign]").exists()).toBe(true)
  })

  test("assignment mutation called with correct variables", async () => {
    const wrapper = makeWrapper({
      roleGroup: "editors",
      mutable: true,
      container: {
        __typename: "Publication",
        id: "1",
        editors: [
          { id: "1", email: "test@example.com", name: "Test" } as User
        ]
      } as Publication
    })

    ;(wrapper.vm as any).user = { id: "1" }
    await (wrapper.vm as any).handleSubmit()
    expect(editorsMutation).toHaveBeenCalledWith({
      connect: ["1"],
      id: "1"
    })
  })

  test.each([
    ["editors", editorsMutation],
    ["publication_admins", publicationAdministratorsMutation]
  ])("Calls %s mutation", async (roleGroup, mock) => {
    const container = {
      id: "1",
      __typename: "Publication"
    } as Publication
    ;(container as Record<string, unknown>)[roleGroup as string] = [
      { id: "1", username: "Test", email: "test@example.com" } as User
    ]
    const wrapper = makeWrapper({
      roleGroup,
      mutable: true,
      container
    })

    ;(wrapper.vm as any).user = { id: "2" }
    await (wrapper.vm as any).handleSubmit()
    expect(mock).toHaveBeenCalledWith({ connect: ["2"], id: "1" })

    mock.mockClear()
    await (wrapper.vm as any).handleUserListClick({ user: { id: "2" } })
    expect(mock).toHaveBeenCalledWith({ disconnect: ["2"], id: "1" })
  })
})

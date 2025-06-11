import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount } from "@vue/test-utils"
import { installApolloClient } from "app/test/vitest/utils"
import { Notify } from "quasar"
import {
  UPDATE_SUBMISSION_REVIEWERS,
  UPDATE_SUBMISSION_REVIEW_COORDINATORS,
  UPDATE_SUBMISSION_SUBMITERS
} from "src/graphql/mutations"
import { beforeEach, describe, expect, test, vi } from "vitest"
import AssignedSubmissionUsers from "./AssignedSubmissionUsers.vue"

installQuasarPlugin({ plugins: { Notify } })
const mockClient = installApolloClient()

describe("AssignedSubmissionUsers", () => {
  const makeWrapper = (props) => {
    return mount(AssignedSubmissionUsers, {
      props
    })
  }
  const reviewersMutation = vi.fn()
  const coordinatorsMutation = vi.fn()
  const submittersMutation = vi.fn()

  mockClient.setRequestHandler(UPDATE_SUBMISSION_REVIEWERS, reviewersMutation)
  mockClient.setRequestHandler(
    UPDATE_SUBMISSION_REVIEW_COORDINATORS,
    coordinatorsMutation
  )
  mockClient.setRequestHandler(UPDATE_SUBMISSION_SUBMITERS, submittersMutation)

  beforeEach(() => {
    vi.resetAllMocks()
  })

  test("shows role users", () => {
    const wrapper = makeWrapper({
      roleGroup: "reviewers",
      container: {
        __typename: "Submission",
        id: 1,
        effective_role: "review_coordinator",
        reviewers: [
          { id: 1, email: "test@example.com", name: "TestUser" },
          { id: 2, email: "test2@example.com", name: "TestUser2" }
        ]
      }
    })
    expect(wrapper).toBeTruthy()
    expect(wrapper.findAll(".q-item")).toHaveLength(2)
  })

  test("shows empty card if no users", async () => {
    const wrapper = makeWrapper({
      roleGroup: "reviewers",
      container: {
        __typename: "Submission",
        id: 1,
        effective_role: "review_coordinator",
        reviewers: []
      }
    })
    expect(wrapper.findComponent({ ref: "card_no_users" }).exists()).toBe(true)

    await wrapper.setProps({
      roleGroup: "reviewers",
      container: {
        __typename: "Submission",
        id: 1,
        effective_role: "review_coordinator",
        reviewers: [{ id: 1, email: "test@example.com", name: "TestUser" }]
      }
    })
    expect(wrapper.findComponent({ ref: "card_no_users" }).exists()).toBe(false)
    expect(wrapper.findAll(".q-item")).toHaveLength(1)
  })

  test("mutable prop hides mutation controls", async () => {
    const wrapper = makeWrapper({
      roleGroup: "reviewers",
      container: {
        __typename: "Submission",
        id: 1,
        effective_role: "review_coordinator",
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }]
      }
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(false)
    expect(wrapper.find("[data-cy=button_unassign]").exists()).toBe(false)

    await wrapper.setProps({
      roleGroup: "reviewers",
      mutable: true,
      container: {
        __typename: "Submission",
        id: 1,
        effective_role: "review_coordinator",
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }]
      }
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(true)
    expect(wrapper.find("[data-cy=button_unassign]").exists()).toBe(true)
  })

  test("maxUsers prop hides assign controls", async () => {
    const wrapper = makeWrapper({
      roleGroup: "reviewers",
      mutable: true,
      maxUsers: 1,
      container: {
        __typename: "Submission",
        id: 1,
        effective_role: "review_coordinator",
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }]
      }
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(false)

    await wrapper.setProps({
      roleGroup: "reviewers",
      mutable: true,
      maxUsers: 2,
      container: {
        __typename: "Submission",
        id: 1,
        effective_role: "review_coordinator",
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }]
      }
    })
    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(true)
  })

  test("mutable props disables mutations", async () => {
    const wrapper = makeWrapper({
      roleGroup: "reviewers",
      container: {
        __typename: "Submission",
        id: 1,
        effective_role: "review_coordinator",
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }]
      }
    })

    wrapper.vm.user = { user: { id: 1 } }
    await wrapper.vm.handleUserListClick({ user: { id: 1 } })
    await wrapper.vm.handleSubmit()

    expect(reviewersMutation).not.toHaveBeenCalled()
  })

  test("maxUsers prop disables assignment mutations, unassign still allowed", async () => {
    const wrapper = makeWrapper({
      roleGroup: "reviewers",
      mutable: true,
      maxUsers: 1,
      container: {
        __typename: "Submission",
        id: 1,
        effective_role: "review_coordinator",
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }]
      }
    })

    wrapper.vm.user = { user: { id: 1 } }
    await wrapper.vm.handleSubmit()
    expect(reviewersMutation).not.toHaveBeenCalled()
    await wrapper.vm.handleUserListClick({ user: { id: 1 } })
    expect(reviewersMutation).toHaveBeenCalledWith({
      disconnect: [1],
      id: 1
    })
  })

  test("assignment mutation called with correct variables", async () => {
    const wrapper = makeWrapper({
      roleGroup: "reviewers",
      mutable: true,
      container: {
        __typename: "Submission",
        id: 1,
        effective_role: "review_coordinator",
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }]
      }
    })
    wrapper.vm.user = { id: 1 }
    await wrapper.vm.handleSubmit()
    expect(reviewersMutation).toHaveBeenCalledWith({
      connect: [1],
      id: 1
    })
  })

  test.each([
    ["reviewers", reviewersMutation],
    ["review_coordinators", coordinatorsMutation],
    ["submitters", submittersMutation]
  ])("Calls %s mutation", async (roleGroup, mock) => {
    const props = {
      roleGroup,
      mutable: true,
      container: {
        id: 1,
        effective_role: "review_coordinator",
        __typename: "Submission"
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

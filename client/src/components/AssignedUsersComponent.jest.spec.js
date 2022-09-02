import AssignedUsers from "./AssignedUsersComponent.vue"
import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import { createMockClient } from "mock-apollo-client"
import { ApolloClients } from "@vue/apollo-composable"
import {
  UPDATE_SUBMISSION_REVIEWERS,
  UPDATE_SUBMISSION_REVIEW_COORDINATORS,
  UPDATE_SUBMISSION_SUBMITERS,
} from "src/graphql/mutations"

jest.mock("quasar", () => ({
  ...jest.requireActual("quasar"),
  useQuasar: () => ({
    notify: jest.fn(),
  }),
}))

installQuasarPlugin()
describe("AssignedUsers", () => {
  const mockClient = createMockClient({
    defaultOptions: { watchQuery: { fetchPolicy: "network-only" } },
  })
  const makeWrapper = (props) => {
    return mount(AssignedUsers, {
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
  const reviewersMutation = jest.fn()
  const coordinatorsMutation = jest.fn()
  const submittersMutation = jest.fn()

  mockClient.setRequestHandler(UPDATE_SUBMISSION_REVIEWERS, reviewersMutation)
  mockClient.setRequestHandler(
    UPDATE_SUBMISSION_REVIEW_COORDINATORS,
    coordinatorsMutation
  )
  mockClient.setRequestHandler(UPDATE_SUBMISSION_SUBMITERS, submittersMutation)

  beforeEach(() => {
    jest.resetAllMocks()
  })

  test("shows relationship users", () => {
    const wrapper = makeWrapper({
      relationship: "reviewers",
      container: {
        __typename: "Submission",
        id: 1,
        reviewers: [
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
      relationship: "reviewers",
      container: {
        __typename: "Submission",
        id: 1,
        reviewers: [],
      },
    })
    expect(wrapper.findComponent({ ref: "card_no_users" }).exists()).toBe(true)

    await wrapper.setProps({
      relationship: "reviewers",
      container: {
        __typename: "Submission",
        id: 1,
        reviewers: [{ id: 1, email: "test@example.com", name: "TestUser" }],
      },
    })
    expect(wrapper.findComponent({ ref: "card_no_users" }).exists()).toBe(false)
    expect(wrapper.findAll(".q-item")).toHaveLength(1)
  })

  test("mutable prop hides mutation controls", async () => {
    const wrapper = makeWrapper({
      relationship: "reviewers",
      container: {
        __typename: "Submission",
        id: 1,
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }],
      },
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(false)
    expect(wrapper.find("[data-cy=button_unassign]").exists()).toBe(false)

    await wrapper.setProps({
      relationship: "reviewers",
      mutable: true,
      container: {
        __typename: "Submission",
        id: 1,
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }],
      },
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(true)
    expect(wrapper.find("[data-cy=button_unassign]").exists()).toBe(true)
  })

  test("maxUsers prop hides assign controls", async () => {
    const wrapper = makeWrapper({
      relationship: "reviewers",
      mutable: true,
      maxUsers: 1,
      container: {
        __typename: "Submission",
        id: 1,
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }],
      },
    })

    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(false)

    await wrapper.setProps({
      relationship: "reviewers",
      mutable: true,
      maxUsers: 2,
      container: {
        __typename: "Submission",
        id: 1,
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }],
      },
    })
    expect(wrapper.findComponent({ name: "QForm" }).exists()).toBe(true)
  })

  test("mutable props disables mutations", async () => {
    const wrapper = makeWrapper({
      relationship: "reviewers",
      container: {
        __typename: "Submission",
        id: 1,
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }],
      },
    })

    wrapper.vm.user = { user: { id: 1 } }
    await wrapper.vm.handleUserListClick({ user: { id: 1 } })
    await wrapper.vm.handleSubmit()

    expect(reviewersMutation).not.toHaveBeenCalled()
  })

  test("maxUsers prop disables assignment mutations, unassign still allowed", async () => {
    const wrapper = makeWrapper({
      relationship: "reviewers",
      mutable: true,
      maxUsers: 1,
      container: {
        __typename: "Submission",
        id: 1,
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }],
      },
    })

    wrapper.vm.user = { user: { id: 1 } }
    await wrapper.vm.handleSubmit()
    expect(reviewersMutation).not.toHaveBeenCalled()
    await wrapper.vm.handleUserListClick({ user: { id: 1 } })
    expect(reviewersMutation).toHaveBeenCalledWith({
      disconnect: [1],
      id: 1,
    })
  })

  test("assignment mutation called with correct variables", async () => {
    const wrapper = makeWrapper({
      relationship: "reviewers",
      mutable: true,
      container: {
        __typename: "Submission",
        id: 1,
        reviewers: [{ id: 1, email: "test@example.com", name: "Test" }],
      },
    })
    wrapper.vm.user = { id: 1 }
    await wrapper.vm.handleSubmit()
    expect(reviewersMutation).toHaveBeenCalledWith({
      connect: [1],
      id: 1,
    })
  })

  test.each([
    ["reviewers", reviewersMutation],
    ["review_coordinators", coordinatorsMutation],
    ["submitters", submittersMutation],
  ])("Calls %s mutation", async (relationship, mock) => {
    const props = {
      relationship,
      mutable: true,
      container: {
        id: 1,
        __typename: "Submission",
      },
    }
    props.container[relationship] = [
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

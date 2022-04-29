import { mount } from "@vue/test-utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-jest"
import SubmissionDetailsPage from "./SubmissionDetails.vue"
import { createMockClient } from "mock-apollo-client"
import { ApolloClients } from "@vue/apollo-composable"
import flushPromises from "flush-promises"
import { GET_SUBMISSION } from "src/graphql/queries"
import {
  CREATE_SUBMISSION_USER,
  DELETE_SUBMISSION_USER,
} from "src/graphql/mutations"

jest.mock("quasar", () => ({
  ...jest.requireActual("quasar"),
  useQuasar: () => ({
    notify: jest.fn(),
  }),
}))

jest.mock("vue-i18n", () => ({
  useI18n: () => ({
    t: (t) => t,
  }),
}))

installQuasarPlugin()

describe("submissions details page mount", () => {
  const mockClient = createMockClient({
    defaultOptions: { watchQuery: { fetchPolicy: "network-only" } },
  })
  const makeWrapper = async () => {
    const wrapper = mount(SubmissionDetailsPage, {
      global: {
        provide: {
          [ApolloClients]: { default: mockClient },
        },
        mocks: {
          $t: (t) => t,
        },
      },
      props: {
        id: "1",
      },
    })
    await flushPromises()
    return wrapper
  }

  const submissionUsersData = {
    submitters: [
      {
        id: "1",
        name: "Jest Submitter 1",
        username: "jestSubmitter1",
        email: "jestsubmitter1@msu.edu",
      },
      {
        id: "5",
        name: "Jest Submitter 2",
        username: "jestSubmitter2",
        email: "jestsubmitter2@msu.edu",
      },
    ],
    reviewers: [
      {
        id: "2",
        name: "Jest Reviewer 1",
        username: "jestReviewer1",
        email: "jestreviewer1@msu.edu",
      },
      {
        id: "3",
        name: "Jest Reviewer 2",
        username: "jestReviewer2",
        email: "jestreviewer2@msu.edu",
      },
      {
        id: "4",
        name: "Jest Reviewer 3 and Review Coordinator 1",
        username: "jestReviewer3Coordinator1",
        email: "jestreviewer3@msu.edu",
      },
    ],
    review_coordinators: [
      {
        id: "4",
        name: "Jest Reviewer 3 and Review Coordinator 1",
        username: "jestReviewer3Coordinator1",
        email: "jestreviewer3@msu.edu",
      },
      {
        id: "6",
        name: "Jest Review Coordinator 2",
        username: "jestCoordinator2",
        email: "jestcoordinator2@msu.edu",
      },
    ],
  }

  const GetSubHandler = jest.fn()
  mockClient.setRequestHandler(GET_SUBMISSION, GetSubHandler)
  const createSubUserHandler = jest.fn()
  mockClient.setRequestHandler(CREATE_SUBMISSION_USER, createSubUserHandler)
  mockClient.setRequestHandler(DELETE_SUBMISSION_USER, createSubUserHandler)

  beforeEach(() => {
    jest.resetAllMocks()
  })

  const defaultApolloMock = () => {
    GetSubHandler.mockResolvedValue({
      data: {
        submission: {
          title: "This Submission",
          publication: {
            name: "Jest Publication",
            style_criterias: [],
          },
          ...submissionUsersData,
        },
      },
    })
  }

  it("mounts without errors", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()
    expect(GetSubHandler).toBeCalledWith({ id: "1" })
    expect(wrapper).toBeTruthy()
  })

  test("all assigned submitters appear within the assigned submitters list", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()

    const list = wrapper.findComponent({ ref: "list_assigned_submitters" })
    expect(list.findAllComponents({ name: "user-list-item" })).toHaveLength(2)
  })

  test("an error message appears when there are no assigned submitters", async () => {
    GetSubHandler.mockResolvedValue({
      data: {
        submission: {
          title: "This submission",
          publication: {
            name: "Jest Publication",
            style_criterias: [],
          },
          reviewers: [],
          review_coordinators: [],
          submitters: [],
        },
      },
    })
    const wrapper = await makeWrapper()
    const card = wrapper.findComponent({ ref: "card_no_submitters" })
    expect(card.text()).toContain("submissions.submitter.none")
  })

  test("all assigned reviewers appear within the assigned reviewers list", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()

    const list = wrapper.findComponent({ ref: "list_assigned_reviewers" })
    expect(list.findAllComponents({ name: "q-item" })).toHaveLength(3)
  })

  test("a default message still appears when there are no assigned reviewers", async () => {
    GetSubHandler.mockResolvedValue({
      data: {
        submission: {
          title: "This submission",
          publication: {
            name: "Jest Publication",
            style_criterias: [],
          },
          reviewers: [],
          review_coordinators: [],
          submitters: [],
        },
      },
    })

    const wrapper = await makeWrapper()
    const card = wrapper.findComponent({ ref: "card_no_reviewers" })
    expect(card.text()).toContain("submissions.reviewer.none")
  })

  test("all assigned review coordinators appear within the assigned review coordinators list", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()
    const list = wrapper.findComponent({
      ref: "list_assigned_review_coordinators",
    })
    expect(list.findAllComponents({ name: "q-item" })).toHaveLength(2)
  })
})

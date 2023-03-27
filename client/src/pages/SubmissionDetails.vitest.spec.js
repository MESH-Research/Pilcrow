import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { installApolloClient } from "test/vitest/utils"
import { GET_SUBMISSION } from "src/graphql/queries"
import { beforeEach, describe, expect, test, vi } from 'vitest'
import SubmissionDetailsPage from "./SubmissionDetails.vue"

vi.mock("quasar", () => ({
  ...vi.requireActual("quasar"),
  useQuasar: () => ({
    notify: vi.fn(),
  }),
}))


installQuasarPlugin()
const mockClient = installApolloClient({
  defaultOptions: { watchQuery: { fetchPolicy: "network-only" } },
})

describe("submissions details page mount", () => {
  beforeEach(() => {
    mockClient.mockReset()
  })

  const makeWrapper = async () => {
    const wrapper = mount(SubmissionDetailsPage, {
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
        __typename: "User",
        id: "1",
        name: "Jest Submitter 1",
        username: "jestSubmitter1",
        email: "jestsubmitter1@msu.edu",
        staged: null,
      },
      {
        __typename: "User",
        id: "5",
        name: "Jest Submitter 2",
        username: "jestSubmitter2",
        email: "jestsubmitter2@msu.edu",
        staged: null,
      },
    ],
    reviewers: [
      {
        __typename: "User",
        id: "2",
        name: "Jest Reviewer 1",
        username: "jestReviewer1",
        email: "jestreviewer1@msu.edu",
        staged: null,
      },
      {
        __typename: "User",
        id: "3",
        name: null,
        username: "jestReviewer2",
        email: "jestReviewer2@msu.edu",
        staged: true,
      },
    ],
    review_coordinators: [
      {
        __typename: "User",
        id: "4",
        name: "Review Coordinator 1",
        username: "jestReviewCoordinator1",
        email: "jestcoordinator1@msu.edu",
        staged: null,
      },
    ],
  }

  const defaultApolloMock = () =>
    mockClient
      .getRequestHandler(GET_SUBMISSION)
      .mockResolvedValue({
        data: {
          submission: {
            id: 1,
            effective_role: "review_coordinator",
            status: 0,
            __typename: "Submission",
            title: "This Submission",
            publication: {
              id: 1,
              name: "Jest Publication",
              style_criterias: [],
            },
            audits: [],
            ...submissionUsersData,
          },
        },
    })

  test("component mounts without errors", async () => {
    const handler = defaultApolloMock()
    const wrapper = await makeWrapper()
    console.log(wrapper.html())
    expect(handler).toHaveBeenCalledWith({ id: "1" })
    expect(wrapper).toBeTruthy()
  })

  test("all assigned submitters appear within the assigned submitters list", async () => {
    const handler = defaultApolloMock()
    const wrapper = await makeWrapper()

    const list = wrapper.find("[data-cy='submitters_list']")
    expect(handler).toHaveBeenCalledWith({ id: "1" })
    expect(list.findAll(".q-item")).toHaveLength(2)
  })

  test("all assigned reviewers appear within the assigned reviewers list", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()

    const list = wrapper.find("[data-cy=reviewers_list]")
    expect(list.findAll(".q-item")).toHaveLength(2)
  })

  test("all assigned review coordinators appear within the assigned review coordinators list", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()
    const list = wrapper.find('[data-cy="coordinators_list"]')
    expect(list.findAll(".q-item")).toHaveLength(1)
  })

  test("staged reviewers are visually indicated to be staged", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()
    const list = wrapper.find('[data-cy="reviewers_list"]')
    expect(list.findAll('[data-cy="user_unconfirmed"]')).toHaveLength(1)
  })
})

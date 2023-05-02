import { GET_SUBMISSION } from "src/graphql/queries"
import { beforeEach, describe, expect, test, vi } from "vitest"
import { installApolloClient } from "test/vitest/utils"
import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { mount, flushPromises } from "@vue/test-utils"
import { useCurrentUser } from "src/use/user"
import { ref } from "vue"
import SubmissionDetailsPage from "./SubmissionDetails.vue"

vi.mock("quasar", () => ({
  ...vi.requireActual("quasar"),
  useQuasar: () => ({
    notify: vi.fn(),
  }),
}))

vi.mock("src/use/user", () => ({
  useCurrentUser: vi.fn(),
}))

installQuasarPlugin()
const mockClient = installApolloClient()

describe("submissions details page mount", () => {
  beforeEach(() => {
    mockClient.mockReset()
  })

  const makeWrapper = () =>
    mount(SubmissionDetailsPage, {
      props: {
        id: "1",
      },
    })

  const submissionUsersData = {
    submitters: [
      {
        __typename: "User",
        id: "1",
        effective_role: "submitter",
        display_label: "Jest Submitter 1",
        name: "Jest Submitter 1",
        username: "jestSubmitter1",
        email: "jestsubmitter1@msu.edu",
        staged: null,
      },
      {
        __typename: "User",
        id: "5",
        effective_role: "submitter",
        display_label: "Jest Submitter 2",
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
        effective_role: "reviewer",
        display_label: "Jest Reviewer 1",
        name: "Jest Reviewer 1",
        username: "jestReviewer1",
        email: "jestreviewer1@msu.edu",
        staged: null,
      },
      {
        __typename: "User",
        id: "3",
        name: null,
        display_label: "Jest Reviewer 2",
        effective_role: "reviewer",
        username: "jestReviewer2",
        email: "jestReviewer2@msu.edu",
        staged: true,
      },
    ],
    review_coordinators: [
      {
        __typename: "User",
        id: "4",
        display_label: "Jest Review Coordinator 2",
        effective_role: "review_coordinator",
        name: "Review Coordinator 1",
        username: "jestReviewCoordinator1",
        email: "jestcoordinator1@msu.edu",
        staged: null,
      },
    ],
  }

  const defaultApolloMock = () =>
    mockClient.getRequestHandler(GET_SUBMISSION).mockResolvedValue({
      data: {
        submission: {
          __typename: "Submission",
          id: 1,
          title: "This Submission",
          status: 0,
          effective_role: "review_coordinator",
          content: {
            data: "",
          },
          audits: [],
          publication: {
            id: 1,
            name: "Jest Publication",
            style_criterias: [],
            editors: [],
            publication_admins: [],
          },
          ...submissionUsersData,
        },
      },
    })

  test.only("component mounts without errors", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({
        __typename: "User",
        id: 1,
        display_label: "Hello",
        name: "Hello",
        email: "hello@example.com",
        username: "helloUser",
        email_verified_at: "2021-08-14 02:26:32",
        roles: [{
          name: "Application Administrator"
        }],
      }),
    })
    const handler = defaultApolloMock()
    const wrapper = await makeWrapper()
    await flushPromises()

    expect(handler).toHaveBeenCalledWith({ id: "1" })
    expect(wrapper).toBeTruthy()
  })

  test("all assigned submitters appear within the assigned submitters list", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({
        id: "1",
        display_label: "",
        username: "",
        name: "",
        email: "",
        email_verified_at: "",
        roles: [],
      }),
    })
    const handler = defaultApolloMock()
    const wrapper = await makeWrapper()
    await flushPromises()

    const list = wrapper.find("[data-cy='submitters_list']")
    expect(handler).toHaveBeenCalledWith({ id: "1" })
    expect(list.findAll(".q-item")).toHaveLength(2)
  })

  test("all assigned reviewers appear within the assigned reviewers list", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({
        id: "1",
        display_label: "",
        username: "",
        name: "",
        email: "",
        email_verified_at: "",
        roles: [],
      }),
    })
    defaultApolloMock()
    const wrapper = await makeWrapper()
    await flushPromises()

    const list = wrapper.find("[data-cy=reviewers_list]")
    expect(list.findAll(".q-item")).toHaveLength(2)
  })

  test("all assigned review coordinators appear within the assigned review coordinators list", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({
        id: "1",
        display_label: "",
        username: "",
        name: "",
        email: "",
        email_verified_at: "",
        roles: [],
      }),
    })
    defaultApolloMock()
    const wrapper = await makeWrapper()
    await flushPromises()

    const list = wrapper.find('[data-cy="coordinators_list"]')
    expect(list.findAll(".q-item")).toHaveLength(1)
  })

  test("staged reviewers are visually indicated to be staged", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({
        id: "1",
        display_label: "",
        username: "",
        name: "",
        email: "",
        email_verified_at: "",
        roles: [],
      }),
    })
    defaultApolloMock()
    const wrapper = await makeWrapper()
    await flushPromises()

    const list = wrapper.find('[data-cy="reviewers_list"]')
    expect(list.findAll('[data-cy="user_unconfirmed"]')).toHaveLength(1)
  })
})

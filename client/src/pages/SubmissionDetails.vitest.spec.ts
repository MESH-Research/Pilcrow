import { GET_SUBMISSION } from "src/graphql/queries"
import type { GetSubmissionQuery } from "src/graphql/generated/graphql"
import {
  SubmissionStatus,
  SubmissionUserRoles
} from "src/graphql/generated/graphql"
import {
  type Mock,
  afterEach,
  beforeEach,
  describe,
  expect,
  test,
  vi
} from "vitest"
import { installApolloClient } from "app/test/vitest/utils"
import { installQuasarPlugin } from "app/test/vitest/utils"
import { mount, flushPromises } from "@vue/test-utils"
import { useCurrentUser } from "src/use/user"
import { ref } from "vue"
import SubmissionDetailsPage from "./SubmissionDetails.vue"

vi.mock("src/use/user", () => ({
  useCurrentUser: vi.fn()
}))

installQuasarPlugin({ plugins: { Notify: vi.fn() } as any })
const mockClient = installApolloClient()

describe("submissions details page mount", () => {
  let wrapper: ReturnType<typeof mount>
  const makeWrapper = () => {
    wrapper = mount(SubmissionDetailsPage, {
      props: {
        id: "1"
      }
    })
    return wrapper
  }

  afterEach(() => {
    wrapper?.unmount()
  })

  const submissionUsersData = {
    submitters: [
      {
        __typename: "User" as const,
        id: "1",
        display_label: "Jest Submitter 1",
        name: "Jest Submitter 1",
        username: "jestSubmitter1",
        email: "jestsubmitter1@msu.edu",
        staged: null
      },
      {
        __typename: "User" as const,
        id: "5",
        display_label: "Jest Submitter 2",
        name: "Jest Submitter 2",
        username: "jestSubmitter2",
        email: "jestsubmitter2@msu.edu",
        staged: null
      }
    ],
    reviewers: [
      {
        __typename: "User" as const,
        id: "2",
        display_label: "Jest Reviewer 1",
        name: "Jest Reviewer 1",
        username: "jestReviewer1",
        email: "jestreviewer1@msu.edu",
        staged: null
      },
      {
        __typename: "User" as const,
        id: "3",
        name: null,
        display_label: "Jest Reviewer 2",
        username: "jestReviewer2",
        email: "jestReviewer2@msu.edu",
        staged: true
      }
    ],
    review_coordinators: [
      {
        __typename: "User" as const,
        id: "4",
        display_label: "Jest Review Coordinator 2",
        name: "Review Coordinator 1",
        username: "jestReviewCoordinator1",
        email: "jestcoordinator1@msu.edu",
        staged: null
      }
    ]
  }

  const defaultApolloMock = () => {
    const mockSubmissionResponse: { data: GetSubmissionQuery } = {
      data: {
        submission: {
          __typename: "Submission",
          id: "1",
          title: "This Submission",
          status: SubmissionStatus.INITIALLY_SUBMITTED,
          effective_role: SubmissionUserRoles.review_coordinator,
          content: {
            data: ""
          },
          audits: [],
          publication: {
            id: "1",
            name: "Jest Publication",
            my_role: null,
            style_criterias: [],
            editors: [],
            publication_admins: []
          },
          ...submissionUsersData
        }
      }
    }
    return mockClient
      .getRequestHandler(GET_SUBMISSION)
      .mockResolvedValue(mockSubmissionResponse)
  }

  const useCurrentUserValue = {
    currentUser: ref({
      __typename: "User",
      id: "1",
      display_label: "Hello",
      name: "Hello",
      email: "hello@example.com",
      username: "helloUser",
      email_verified_at: "2021-08-14 02:26:32",
      roles: [
        {
          name: "Application Administrator"
        }
      ]
    }),
    isAppAdmin: ref(true),
    isSubmitter: () => true,
    isReviewCoordinator: () => false,
    isEditor: () => false,
    isPublicationAdmin: () => false
  }

  beforeEach(() => {
    mockClient.mockReset()
    ;(useCurrentUser as Mock).mockReturnValue(useCurrentUserValue)
  })

  test("component mounts without errors", async () => {
    const handler = defaultApolloMock()
    const wrapper = await makeWrapper()
    await flushPromises()

    expect(handler).toHaveBeenCalledWith({ id: "1" })
    expect(wrapper).toBeTruthy()
  })

  test("all assigned submitters appear within the assigned submitters list", async () => {
    const handler = defaultApolloMock()
    const wrapper = await makeWrapper()
    await flushPromises()

    const list = wrapper.find("[data-cy='submitters_list']")
    expect(handler).toHaveBeenCalledWith({ id: "1" })
    expect(list.findAll(".q-item")).toHaveLength(2)
  })

  test("all assigned reviewers appear within the assigned reviewers list", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()
    await flushPromises()

    const list = wrapper.find("[data-cy=reviewers_list]")
    expect(list.findAll(".q-item")).toHaveLength(2)
  })

  test("all assigned review coordinators appear within the assigned review coordinators list", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()
    await flushPromises()

    const list = wrapper.find('[data-cy="coordinators_list"]')
    expect(list.findAll(".q-item")).toHaveLength(1)
  })

  test("staged reviewers are visually indicated to be staged", async () => {
    defaultApolloMock()
    const wrapper = await makeWrapper()
    await flushPromises()

    const list = wrapper.find('[data-cy="reviewers_list"]')
    expect(list.findAll('[data-cy="user_unconfirmed"]')).toHaveLength(1)
  })
})

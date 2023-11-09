import { installQuasarPlugin } from "@quasar/quasar-app-extension-testing-unit-vitest"
import { flushPromises, mount } from "@vue/test-utils"
import { installApolloClient } from "test/vitest/utils"
import { CURRENT_USER_SUBMISSIONS, GET_SUBMISSIONS } from "src/graphql/queries"
import { ref } from "vue"
import { useCurrentUser } from "src/use/user"
import ReviewsPage from "./ReviewsPage.vue"

import { describe, expect, test, vi, afterEach } from "vitest"

vi.mock("src/use/user", () => ({
  useCurrentUser: vi.fn(),
}))

installQuasarPlugin()
const mockClient = installApolloClient()

describe("Reviews Page", () => {
  const makeWrapper = () =>
    mount(ReviewsPage, {
      global: {
        stubs: ["router-link", "i18n-t"],
      },
    })

  afterEach(() => {
    vi.clearAllMocks()
  })

  test("two submission tables appear for a user as an editor", async () => {
    useCurrentUser.mockReturnValue({
      currentUser: ref({ id: 1 }),
    })
    mockClient.getRequestHandler(GET_SUBMISSIONS).mockResolvedValue({
      data: {
        submissions: {
          paginatorInfo: {
            count: 1,
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
          },
          data: [
            {
              id: "1001",
              title: "Jest Submission 1001",
              created_at: "2023-11-09T18:51:10.000000Z",
              status: "UNDER_REVIEW",
              my_role: null,
              effective_role: "review_coordinator",
              publication: {
                id: "1000",
                name: "Jest Publication",
                my_role: "editor",
                editors: [
                  {
                    id: "300",
                    display_label: "Jest Editor",
                    username: "jestEditor",
                    name: "Jest Editor",
                    email: "jesteditor@meshresearch.net",
                    staged: null,
                  },
                ],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
          ],
        },
      },
    })

    mockClient.getRequestHandler(CURRENT_USER_SUBMISSIONS).mockResolvedValue({
      data: {
        currentUser: {
          id: 1000,
          roles: [],
          highest_privileged_role: "editor",
          submissions: [
            {
              id: "1000",
              title: "Jest Submission 1000",
              created_at: "2023-06-27T08:21:44.000000Z",
              status: "INITIALLY_SUBMITTED",
              my_role: null,
              effective_role: "review_coordinator",
              publication: {
                id: "1000",
                name: "Jest Publication",
                my_role: null,
                editors: [
                  {
                    id: "300",
                    display_label: "Jest Editor",
                    username: "jestEditor",
                    name: "Jest Editor",
                    email: "jesteditor@meshresearch.net",
                    staged: null,
                  },
                ],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
          ],
        },
      },
    })

    const wrapper = makeWrapper()
    await flushPromises()

    expect(wrapper.findAllComponents({ name: "submission-table" }).length).toBe(
      2,
    )
  })

  test("two submission tables appear for a user as a review coordinator", async () => {
    mockClient.getRequestHandler(GET_SUBMISSIONS).mockResolvedValue({
      data: {
        submissions: {
          paginatorInfo: {
            count: 1,
            currentPage: 1,
            lastPage: 1,
            perPage: 10,
          },
          data: []
        },
      }
    })
    mockClient.getRequestHandler(CURRENT_USER_SUBMISSIONS).mockResolvedValue({
      data: {
        currentUser: {
          id: 1000,
          roles: {
            name: "Application Administrator",
          },
          highest_privileged_role: "application_admin",
          submissions: [
            {
              id: "1",
              title: "Jest Submission 1",
              created_at: "2023-06-27T08:21:44.000000Z",
              status: "INITIALLY_SUBMITTED",
              my_role: "submitter",
              effective_role: "submitter",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
                editors: [],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
            {
              id: "2",
              title: "Jest Submission 2",
              created_at: "2023-06-26T08:21:44.000000Z",
              status: "RESUBMISSION_REQUESTED",
              my_role: "reviewer",
              effective_role: "reviewer",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
                editors: [],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
            {
              id: "3",
              title: "Jest Submission 3",
              created_at: "2023-06-25T08:21:44.000000Z",
              status: "AWAITING_REVIEW",
              my_role: "review_coordinator",
              effective_role: "review_coordinator",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
                editors: [],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
            {
              id: "4",
              title: "Jest Submission 4",
              created_at: "2023-06-24T08:21:44.000000Z",
              status: "REJECTED",
              my_role: "review_coordinator",
              effective_role: "review_coordinator",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
                editors: [],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
            {
              id: "5",
              title: "Jest Submission 5",
              created_at: "2023-06-23T08:21:44.000000Z",
              status: "INITIALLY_SUBMITTED",
              my_role: "reviewer",
              effective_role: "reviewer",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
                editors: [],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
          ],
        },
      },
    })

    const wrapper = makeWrapper()
    await flushPromises()

    expect(wrapper.findAllComponents({ name: "submission-table" }).length).toBe(
      2,
    )
  })

  test("only one submission table appears for a user as a reviewer", async () => {
    mockClient.getRequestHandler(CURRENT_USER_SUBMISSIONS).mockResolvedValue({
      data: {
        currentUser: {
          id: 1000,
          roles: {
            name: "Application Administrator",
          },
          highest_privileged_role: "application_admin",
          submissions: [
            {
              id: "1",
              title: "Jest Submission 1",
              created_at: "2023-06-27T08:21:44.000000Z",
              status: "INITIALLY_SUBMITTED",
              my_role: "submitter",
              effective_role: "submitter",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
                editors: [],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
            {
              id: "2",
              title: "Jest Submission 2",
              created_at: "2023-06-26T08:21:44.000000Z",
              status: "RESUBMISSION_REQUESTED",
              my_role: "reviewer",
              effective_role: "reviewer",
              publication: {
                id: "1",
                name: "Jest Publication",
                my_role: null,
                editors: [],
                publication_admins: [],
              },
              inline_comments: [],
              overall_comments: [],
              submitters: [],
              reviewers: [],
              review_coordinators: [],
            },
          ],
        },
      },
    })
    const wrapper = makeWrapper()
    expect(wrapper.findAllComponents({ name: "submission-table" }).length).toBe(
      1,
    )
  })
})
